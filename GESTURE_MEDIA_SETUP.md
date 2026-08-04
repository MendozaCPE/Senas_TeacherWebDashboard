# Create a quick guide for your friend
echo "# Gesture Media Setup

## 1. Run migrations
\`\`\`bash
php artisan migrate
\`\`\`

## 2. Get the media files
The media files are not in git. You need to:
- Download sign_language_media.zip from [share link]
- Extract to: storage/app/public/sign_language_media/

OR

- Copy from the shared drive if available

## 3. Verify it works
Check that files are accessible:
\`\`\`bash
ls -la storage/app/public/sign_language_media/
\`\`\`

## 4. If files are already in place, run:
\`\`\`bash
php artisan gesture:import-media
\`\`\`

## Media Structure
- storage/app/public/sign_language_media/Alphabets/
- storage/app/public/sign_language_media/Numbers/
- storage/app/public/sign_language_media/Greetings/
- storage/app/public/sign_language_media/Survival/

## Access URLs
- Local: http://localhost:8000/storage/sign_language_media/...
- Production: https://yourdomain.com/storage/sign_language_media/...
" > GESTURE_MEDIA_SETUP.md

# Add it to git
git add GESTURE_MEDIA_SETUP.md