<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SampleAnnouncementSeeder extends Seeder
{
    public function run()
    {
        if (ENVIRONMENT === 'production') {
            throw new \RuntimeException('SampleAnnouncementSeeder may not be run in production.');
        }

        $samples = [
            [
                'title' => 'Student Council Election Schedule (Sample)',
                'message' => 'The student council election will take place on October 28 from 8:00 AM to 4:00 PM. Please sign in to KEVS during the voting period to review the candidates and cast your vote.',
                'image_path' => 'uploads/announcements/1785982296_30b113571766ebab9e90.png',
            ],
            [
                'title' => 'Check Your Student Account Before Election Day (Sample)',
                'message' => 'Make sure you can access your student account before voting begins. If your account details need updating or you cannot sign in, contact the school administrator for assistance.',
                'image_path' => 'uploads/announcements/1785982597_9f87f253c7cd1b21990c.png',
            ],
            [
                'title' => 'Prepare Your Student ID for Verification (Sample)',
                'message' => 'Keep your student ID available when accessing the voting system. Face verification requires a working camera, good lighting, and a clear view of your face.',
                'image_path' => 'uploads/announcements/1785992125_3653b77af2c47606ce8a.png',
            ],
            [
                'title' => 'How to Cast Your Vote (Sample)',
                'message' => 'Sign in to KEVS, open the active election, review each position and candidate, then submit your selections. Confirm your choices before submitting because a submitted ballot cannot be changed.',
                'image_path' => 'uploads/announcements/1792133977_3cf3d07463bb32bdb36a.png',
            ],
            [
                'title' => 'Need Help with Online Voting? (Sample)',
                'message' => 'For help with account access, face verification, or the voting process, contact the school election committee or administrator. Do not share your password with anyone.',
                'image_path' => 'uploads/announcements/1792133947_ce3bde1e8f6858446a4c.png',
            ],
        ];

        $table = $this->db->table('announcements');
        $now = date('Y-m-d H:i:s');

        foreach ($samples as $sample) {
            $imagePath = is_file(FCPATH . $sample['image_path']) ? $sample['image_path'] : null;
            $existing = $table
                ->where('title', $sample['title'])
                ->get()
                ->getRowArray();

            if ($existing !== null) {
                if (empty($existing['image_path']) && $imagePath !== null) {
                    $table->where('id', $existing['id'])->update(['image_path' => $imagePath]);
                }
                continue;
            }

            $table->insert([
                'title' => $sample['title'],
                'message' => $sample['message'],
                'is_active' => 1,
                'image_path' => $imagePath,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
