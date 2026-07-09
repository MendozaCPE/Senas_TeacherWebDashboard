# Requirements Document

## Introduction

The AI Lesson Generator adds an AI-powered authoring shortcut to the SENAS Teacher Web Dashboard. Teachers click "✨ Generate with AI" on the Create Lesson page, fill in a short form (topic, difficulty, lesson type, number of slides, optional special instructions), and receive a fully structured FSL lesson — title, description, content slides, and quiz — that auto-populates the existing lesson creator form. The AI uses DeepSeek (via OpenRouter) as its language model. Real gesture images/videos are resolved from the `gestures` database table and attached to matching content slides. Slides with no matching gesture are flagged `media_missing = true`, showing a "⚠ No Media Available" badge in the Edit view alongside an upload input so teachers can supply the media themselves.

## Glossary

- **AI_Lesson_Generator**: The overall feature described in this document.
- **DeepSeekService**: The Laravel service class that calls the DeepSeek model via OpenRouter's chat completions API.
- **GestureMediaResolver**: The Laravel service class that queries the `gestures` table and attaches `image_url`/`video_url` to content slides that contain a matching `gesture_name`.
- **LessonsController**: The existing Laravel controller (`app/Http/Controllers/LessonsController.php`) that is extended to handle AI generation requests.
- **AI_Generator_Modal**: The slide-in modal presented on the Create Lesson page where teachers configure AI generation parameters.
- **Teacher**: An authenticated user of the SENAS Teacher Web Dashboard.
- **Lesson**: A record in the `lessons` table managed by `LessonsController`, identified by `lesson_id`.
- **LessonContent**: A record in the `lesson_contents` table representing a single content slide within a lesson.
- **Quiz**: A structured set of multiple-choice questions stored across the `quizzes`, `quiz_questions`, and `quiz_options` tables.
- **Gesture**: A record in the `gestures` table that stores `name` (snake_case), `image_url`, and `video_url` for a Filipino Sign Language gesture.
- **OpenRouter**: The API gateway used to access the DeepSeek language model. Base URL: `https://openrouter.ai/api/v1`.
- **media_missing**: A boolean column on `lesson_contents` that is `true` when the AI requested a gesture but no matching record was found in the `gestures` table.
- **ai_generated**: A boolean column on `lessons` that records whether the lesson was initially created via the AI generator.
- **ai_prompt**: A TEXT column on `lessons` that stores the topic/instructions submitted to the AI for audit and regeneration purposes.

---

## Requirements

### Requirement 1: AI Generation Trigger — "Generate with AI" Button

**User Story:** As a Teacher, I want a clearly visible "✨ Generate with AI" button on the Create Lesson page, so that I can quickly access the AI lesson generator without searching for it.

#### Acceptance Criteria

1. THE Create_Lesson_Page SHALL render a "✨ Generate with AI" button in the page header area, visually distinct from the Cancel button and styled with a purple-to-indigo gradient to draw attention.
2. WHEN the Teacher clicks the "✨ Generate with AI" button, THE AI_Generator_Modal SHALL slide open without a full page reload.
3. THE Create_Lesson_Page SHALL include the `ai-generator-modal` partial (`resources/views/lessons/partials/ai-generator-modal.blade.php`).

---

### Requirement 2: AI Generator Modal — Configuration Form

**User Story:** As a Teacher, I want a modal form to configure the AI generation parameters, so that the generated lesson matches my teaching needs.

#### Acceptance Criteria

1. THE AI_Generator_Modal SHALL contain a text input for **Topic** (required, maximum 200 characters).
2. THE AI_Generator_Modal SHALL contain a dropdown for **Difficulty** with exactly three options: `beginner`, `intermediate`, `advanced`.
3. THE AI_Generator_Modal SHALL contain a dropdown for **Lesson Type** with options matching the existing lesson types: `gesture`, `text`, `interactive`.
4. THE AI_Generator_Modal SHALL contain a numeric input for **Number of Slides** with a minimum value of 3 and a maximum value of 10.
5. THE AI_Generator_Modal SHALL contain an optional textarea for **Special Instructions** (maximum 500 characters).
6. THE AI_Generator_Modal SHALL contain a "Generate" button that submits the generation request and a "Cancel" button that closes the modal without any action.
7. WHEN the Teacher clicks "Generate", THE AI_Generator_Modal SHALL display a loading spinner and the message "Generating your lesson..." while the AJAX request is in progress.
8. WHILE the generation request is in progress, THE "Generate" button SHALL be disabled to prevent duplicate submissions.
9. IF the generation request returns an error, THEN THE AI_Generator_Modal SHALL display a human-readable error message and re-enable the "Generate" button.

---

### Requirement 3: AI Generation Endpoint

**User Story:** As a Teacher, I want the backend to accept my generation parameters and return a complete structured lesson, so that the form can be auto-populated.

#### Acceptance Criteria

1. THE LessonsController SHALL expose a route `POST /lessons/ai-generate` mapped to the `aiGenerate()` method, protected by the `auth` middleware.
2. WHEN a POST request is received at `/lessons/ai-generate`, THE LessonsController SHALL validate the request body and reject requests with a 422 response when: `topic` is missing or exceeds 200 characters; `difficulty` is not one of `beginner`, `intermediate`, `advanced`; `lesson_type` is not one of `gesture`, `text`, `interactive`; `num_slides` is not an integer between 3 and 10 inclusive.
3. WHEN the request passes validation, THE LessonsController SHALL call `DeepSeekService::generate()` with the validated parameters.
4. WHEN `DeepSeekService::generate()` returns a valid lesson structure, THE LessonsController SHALL call `GestureMediaResolver::resolve()` to attach media URLs to content slides.
5. WHEN media resolution is complete, THE LessonsController SHALL return a 200 JSON response containing the full lesson structure including resolved media URLs and `media_missing` flags.
6. IF `DeepSeekService::generate()` throws an exception or the DeepSeek API returns a non-200 status, THEN THE LessonsController SHALL return a 500 JSON response with a `message` key containing a user-friendly error string.

---

### Requirement 4: DeepSeek API Integration

**User Story:** As a Teacher, I want the system to call DeepSeek AI to generate structured lesson content, so that the AI produces educationally sound FSL lessons.

#### Acceptance Criteria

1. THE DeepSeekService SHALL read the API key from `config('services.deepseek.api_key')`, the model from `config('services.deepseek.model')`, and the base URL from `config('services.deepseek.base_url')`.
2. WHEN `DeepSeekService::generate()` is called, THE DeepSeekService SHALL send an HTTP POST request to `{base_url}/chat/completions` with the `Authorization: Bearer {api_key}` header and `Content-Type: application/json`.
3. THE DeepSeekService SHALL construct a system prompt that instructs the model to act as an expert FSL curriculum designer and to respond with ONLY valid JSON matching the defined lesson schema (no markdown, no explanation).
4. THE DeepSeekService SHALL construct a user prompt that includes the topic, difficulty, lesson type, number of slides, and optional special instructions supplied by the Teacher.
5. THE DeepSeekService SHALL specify `"response_format": {"type": "json_object"}` in the API request body to enforce JSON output.
6. WHEN the API response is received, THE DeepSeekService SHALL parse and validate that the JSON contains `title` (string), `description` (string), `lesson_type` (string), `difficulty` (string), `contents` (array with at least one element), and `quiz` (array with exactly 5 elements).
7. IF the parsed JSON fails validation or cannot be decoded, THEN THE DeepSeekService SHALL throw a `\RuntimeException` with a descriptive message.
8. THE DeepSeekService SHALL set an HTTP timeout of 60 seconds for the API request.

---

### Requirement 5: Gesture Media Resolution

**User Story:** As a Teacher, I want the system to automatically attach real gesture images and videos to AI-generated slides where possible, so that students have visual references without me manually finding them.

#### Acceptance Criteria

1. WHEN `GestureMediaResolver::resolve()` is called with the AI-generated lesson array, THE GestureMediaResolver SHALL iterate over each content slide that has a non-null `gesture_name` value.
2. FOR EACH content slide with a `gesture_name`, THE GestureMediaResolver SHALL query the `gestures` table for a record where `name` equals the `gesture_name` value using a case-insensitive match.
3. WHEN a matching Gesture record is found, THE GestureMediaResolver SHALL set `image_url` to `$gesture->image_url` and `video_url` to `$gesture->video_url` on the content slide and set `media_missing` to `false`.
4. WHEN no matching Gesture record is found, THE GestureMediaResolver SHALL set `media_missing` to `true` and leave `image_url` and `video_url` as `null` on the content slide.
5. THE GestureMediaResolver SHALL return the complete modified lesson array with all slides updated.

---

### Requirement 6: Frontend Form Auto-Population

**User Story:** As a Teacher, I want the lesson creator form to be automatically filled in after AI generation, so that I can review and edit the result without re-typing everything.

#### Acceptance Criteria

1. WHEN the AJAX response from `/lessons/ai-generate` returns with HTTP 200, THE Create_Lesson_Page JavaScript SHALL close the AI_Generator_Modal.
2. WHEN the modal closes after a successful response, THE Create_Lesson_Page SHALL populate the `title` input with `response.title`.
3. WHEN the modal closes after a successful response, THE Create_Lesson_Page SHALL populate the `description` textarea with `response.description`.
4. WHEN the modal closes after a successful response, THE Create_Lesson_Page SHALL set the `difficulty` select to `response.difficulty`.
5. WHEN the modal closes after a successful response, THE Create_Lesson_Page SHALL set the `lesson_type` select to `response.lesson_type`.
6. WHEN the modal closes after a successful response, THE Create_Lesson_Page SHALL replace all existing content slides in `#contentCards` with new slides generated from `response.contents`, preserving the correct `contents[index][...]` input naming convention.
7. FOR EACH content slide in `response.contents`, THE Create_Lesson_Page SHALL render the slide's `title`, `content_text`, `content_type`, and `gesture_name`, and when `media_missing` is `true` SHALL display a yellow "⚠ No Media Available" badge on that slide.
8. WHEN the modal closes after a successful response, THE Create_Lesson_Page SHALL replace all existing quiz questions in `#quizQuestions` with new questions generated from `response.quiz`, including question text and four answer options with the correct answer pre-selected.
9. THE Create_Lesson_Page SHALL allow the Teacher to freely edit all auto-populated fields before saving or publishing.

---

### Requirement 7: Database Schema Changes

**User Story:** As a system administrator, I want the database to track whether a lesson was AI-generated and flag content slides missing media, so that teachers can follow up on incomplete slides.

#### Acceptance Criteria

1. THE Migration `add_ai_fields_to_lessons` SHALL add a `ai_generated` column of type `TINYINT(1)` with a default of `0` to the `lessons` table.
2. THE Migration `add_ai_fields_to_lessons` SHALL add an `ai_prompt` column of type `TEXT` that is nullable to the `lessons` table.
3. THE Migration `add_media_missing_to_lesson_contents` SHALL add a `media_missing` column of type `TINYINT(1)` with a default of `0` to the `lesson_contents` table.
4. THE `Lesson` model SHALL include `ai_generated` and `ai_prompt` in its `$fillable` array.
5. THE `LessonContent` model SHALL include `media_missing` in its `$fillable` array.

---

### Requirement 8: Edit View — "No Media Available" Badge and Upload

**User Story:** As a Teacher, I want to see which AI-generated content slides are missing media when I open the Edit view, so that I can upload the missing files myself.

#### Acceptance Criteria

1. WHEN the Edit Lesson page renders a content slide where `media_missing` is `true`, THE Edit_Lesson_Page SHALL display a yellow "⚠ No Media Available" badge prominently on that slide.
2. WHEN `media_missing` is `true` on a content slide, THE Edit_Lesson_Page SHALL render a visible file upload input labelled "Upload Media" on that slide so the Teacher can supply the missing file.
3. WHEN the Teacher uploads a file and saves the lesson via the existing Update flow, THE LessonsController SHALL store the uploaded file to public storage and set `media_url` to the stored path on the updated `LessonContent` record.
4. WHEN a file is successfully uploaded and saved, THE `LessonContent` record SHALL have `media_missing` set to `0` (false).
5. THE Edit_Lesson_Page SHALL pass a hidden input `contents[index][media_missing]` so the Update controller action can persist the `media_missing` state for slides where no new file is uploaded.

---

### Requirement 9: Environment and Configuration

**User Story:** As a developer, I want all DeepSeek API credentials stored in environment variables, so that secrets are not hard-coded in the codebase.

#### Acceptance Criteria

1. THE `.env` file SHALL define `DEEPSEEK_API_KEY`, `DEEPSEEK_MODEL`, and `DEEPSEEK_BASE_URL`.
2. THE `config/services.php` file SHALL expose a `deepseek` key containing `api_key`, `model`, and `base_url` values read from the corresponding environment variables.
3. THE `.env.example` file SHALL include placeholder entries for `DEEPSEEK_API_KEY`, `DEEPSEEK_MODEL`, and `DEEPSEEK_BASE_URL` without real values.
4. THE DeepSeekService SHALL NOT contain any hard-coded API keys or URLs.
