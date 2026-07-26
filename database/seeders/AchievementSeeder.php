<?php

namespace Database\Seeders;   

use Illuminate\Database\Seeder;
use App\Models\Achievement;

class AchievementSeeder extends Seeder
{
    public function run()
    {
        $achievements = [
            // XP Achievements
            ['code' => 'xp_50', 'name' => 'XP Collector', 'description' => 'Earn 50 XP', 'category' => 'xp', 'icon' => '🪙', 'color' => '#10B981', 'order' => 1, 'criteria' => [['type' => 'xp', 'threshold' => 50]]],
            ['code' => 'xp_100', 'name' => 'XP Enthusiast', 'description' => 'Earn 100 XP', 'category' => 'xp', 'icon' => '⭐', 'color' => '#F59E0B', 'order' => 2, 'criteria' => [['type' => 'xp', 'threshold' => 100]]],
            ['code' => 'xp_250', 'name' => 'XP Warrior', 'description' => 'Earn 250 XP', 'category' => 'xp', 'icon' => '⚔️', 'color' => '#EF4444', 'order' => 3, 'criteria' => [['type' => 'xp', 'threshold' => 250]]],
            ['code' => 'xp_500', 'name' => 'XP Master', 'description' => 'Earn 500 XP', 'category' => 'xp', 'icon' => '👑', 'color' => '#8B5CF6', 'order' => 4, 'criteria' => [['type' => 'xp', 'threshold' => 500]]],
            ['code' => 'xp_1000', 'name' => 'XP Legend', 'description' => 'Earn 1,000 XP', 'category' => 'xp', 'icon' => '🏆', 'color' => '#EC4899', 'order' => 5, 'criteria' => [['type' => 'xp', 'threshold' => 1000]]],
            ['code' => 'xp_2500', 'name' => 'XP Elite', 'description' => 'Earn 2,500 XP', 'category' => 'xp', 'icon' => '💎', 'color' => '#06B6D4', 'order' => 6, 'criteria' => [['type' => 'xp', 'threshold' => 2500]]],
            ['code' => 'xp_5000', 'name' => 'XP Grandmaster', 'description' => 'Earn 5,000 XP', 'category' => 'xp', 'icon' => '🌟', 'color' => '#F97316', 'order' => 7, 'criteria' => [['type' => 'xp', 'threshold' => 5000]]],
            
            // Beginner Achievements
            ['code' => 'beginner_welcome', 'name' => 'First Step', 'description' => 'Complete your first lesson', 'category' => 'beginner', 'icon' => '👣', 'color' => '#10B981', 'order' => 10, 'criteria' => [['type' => 'lessons_completed', 'threshold' => 1]]],
            ['code' => 'beginner_5_lessons', 'name' => 'Rising Beginner', 'description' => 'Complete 5 beginner lessons', 'category' => 'beginner', 'icon' => '📚', 'color' => '#F59E0B', 'order' => 11, 'criteria' => [['type' => 'lessons_completed', 'threshold' => 5, 'filters' => ['difficulty' => 'Beginner']]]],
            ['code' => 'beginner_10_lessons', 'name' => 'Dedicated Beginner', 'description' => 'Complete 10 beginner lessons', 'category' => 'beginner', 'icon' => '📖', 'color' => '#EF4444', 'order' => 12, 'criteria' => [['type' => 'lessons_completed', 'threshold' => 10, 'filters' => ['difficulty' => 'Beginner']]]],
            ['code' => 'alphabet_master', 'name' => 'Alphabet Star', 'description' => 'Master all 26 FSL alphabet signs', 'category' => 'beginner', 'icon' => '🔤', 'color' => '#06B6D4', 'order' => 13, 'criteria' => [['type' => 'gesture_mastered', 'threshold' => 26, 'filters' => ['module_name' => 'alphabet_part1']]]],
            ['code' => 'numbers_master', 'name' => 'Number Ninja', 'description' => 'Learn numbers 1-10', 'category' => 'beginner', 'icon' => '🔢', 'color' => '#F97316', 'order' => 14, 'criteria' => [['type' => 'gesture_mastered', 'threshold' => 10, 'filters' => ['module_name' => 'numbers']]]],
            
            // Intermediate Achievements
            ['code' => 'intermediate_reached', 'name' => 'Level Up! 🚀', 'description' => 'Reach Intermediate level', 'category' => 'intermediate', 'icon' => '🚀', 'color' => '#2563EB', 'order' => 20, 'criteria' => [['type' => 'level', 'threshold' => 1, 'filters' => ['target_level' => 'Intermediate']]]],
            ['code' => 'intermediate_5_lessons', 'name' => 'Rising Intermediate', 'description' => 'Complete 5 intermediate lessons', 'category' => 'intermediate', 'icon' => '📚', 'color' => '#8B5CF6', 'order' => 21, 'criteria' => [['type' => 'lessons_completed', 'threshold' => 5, 'filters' => ['difficulty' => 'Intermediate']]]],
            ['code' => 'intermediate_10_lessons', 'name' => 'Dedicated Intermediate', 'description' => 'Complete 10 intermediate lessons', 'category' => 'intermediate', 'icon' => '📖', 'color' => '#EC4899', 'order' => 22, 'criteria' => [['type' => 'lessons_completed', 'threshold' => 10, 'filters' => ['difficulty' => 'Intermediate']]]],
            ['code' => 'greetings_master', 'name' => 'Greeter Expert', 'description' => 'Complete the Greetings module', 'category' => 'intermediate', 'icon' => '👋', 'color' => '#F59E0B', 'order' => 23, 'criteria' => [['type' => 'modules_completed', 'threshold' => 1, 'filters' => ['module_name' => 'level2_greetings']]]],
            
            // Advanced Achievements
            ['code' => 'advanced_reached', 'name' => 'Advanced Signer! 🎯', 'description' => 'Reach Advanced level', 'category' => 'advanced', 'icon' => '🎯', 'color' => '#EF4444', 'order' => 30, 'criteria' => [['type' => 'level', 'threshold' => 1, 'filters' => ['target_level' => 'Advanced']]]],
            ['code' => 'advanced_5_lessons', 'name' => 'Rising Advanced', 'description' => 'Complete 5 advanced lessons', 'category' => 'advanced', 'icon' => '📚', 'color' => '#D97706', 'order' => 31, 'criteria' => [['type' => 'lessons_completed', 'threshold' => 5, 'filters' => ['difficulty' => 'Advanced']]]],
            
            // Graduation Achievements
            ['code' => 'graduated', 'name' => 'GRADUATED! 🎓🎉', 'description' => 'Complete your FSL journey!', 'category' => 'graduation', 'icon' => '🎓', 'color' => '#F97316', 'order' => 40, 'criteria' => [['type' => 'level', 'threshold' => 1, 'filters' => ['target_level' => 'Graduated']]]],
            
            // Special Achievements
            ['code' => 'streak_3', 'name' => 'Streak Starter', 'description' => 'Practice 3 days in a row', 'category' => 'special', 'icon' => '🔥', 'color' => '#EF4444', 'order' => 50, 'criteria' => [['type' => 'streak_days', 'threshold' => 3]]],
            ['code' => 'streak_7', 'name' => 'Week Warrior', 'description' => 'Practice 7 days in a row', 'category' => 'special', 'icon' => '⚡', 'color' => '#F59E0B', 'order' => 51, 'criteria' => [['type' => 'streak_days', 'threshold' => 7]]],
            ['code' => 'streak_30', 'name' => 'Monthly Master', 'description' => 'Practice 30 days in a row', 'category' => 'special', 'icon' => '🏅', 'color' => '#8B5CF6', 'order' => 52, 'criteria' => [['type' => 'streak_days', 'threshold' => 30]]],
            ['code' => 'quiz_whiz', 'name' => 'Quiz Whiz', 'description' => 'Score 100% on any quiz', 'category' => 'special', 'icon' => '🧠', 'color' => '#10B981', 'order' => 53, 'criteria' => [['type' => 'perfect_scores', 'threshold' => 1]]],
            ['code' => 'leaderboard_top', 'name' => '#1 Champion', 'description' => 'Reach #1 on any leaderboard', 'category' => 'special', 'icon' => '👑', 'color' => '#F97316', 'order' => 54, 'criteria' => [['type' => 'leaderboard_top', 'threshold' => 1]]],
            ['code' => 'all_badges', 'name' => 'Complete Collector', 'description' => 'Earn all achievements', 'category' => 'special', 'icon' => '🏆', 'color' => '#EC4899', 'order' => 55, 'criteria' => [['type' => 'all_achievements', 'threshold' => 1]]],
        ];
        
        foreach ($achievements as $data) {
            Achievement::create($data);
        }
    }
}