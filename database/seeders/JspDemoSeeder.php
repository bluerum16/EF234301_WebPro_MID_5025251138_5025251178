<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/** Synthetic fixtures for a NEW local database; existing records are untouched. */
class JspDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('users')->exists()) {
            return;
        }
        DB::transaction(function () {
            $users = [
                ['Administrator', 'admin@mybook.test', 'admin', 'AdminDemo123!'],
                ['Nadia Putri', 'librarian@mybook.test', 'librarian', 'Librarian123!'],
            ];
            foreach (
                ['Alya Ramadhani', 'Bima Pratama', 'Citra Maharani', 'Dimas Saputra', 'Eva Kusuma'] as $i => $name
            ) {
                $users[] = [$name, 'member'.($i + 1).'@mybook.test', 'member', 'MemberDemo123!'];
            }
            foreach ($users as [$name, $email, $role, $password]) {
                DB::table('users')->insert([
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'password' => Hash::make($password),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'phone' => '',
                    'address' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            foreach (['Fiction', 'Self Improvement', 'Technology', 'Science', 'History'] as $name) {
                DB::table('categories')->insert([
                    'name' => $name,
                    'description' => 'Collection: '.$name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $authors = [
                ['Andrea Hirata', 'Indonesia'],
                ['James Clear', 'United States'],
                ['Robert C. Martin', 'United States'],
                ['Morgan Housel', 'United States'],
                ['Yuval Noah Harari', 'Israel'],
                ['Dee Lestari', 'Indonesia'],
                ['Cal Newport', 'United States'],
                ['Matt Haig', 'United Kingdom'],
            ];
            foreach ($authors as [$name, $country]) {
                DB::table('authors')->insert([
                    'name' => $name,
                    'country' => $country,
                    'biography' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            foreach (
                [
                    ['Bentang Pustaka', 'Yogyakarta', 'bentang'],
                    ['Gramedia Pustaka Utama', 'Jakarta', 'gramedia'],
                    ['Prentice Hall', 'Boston', 'prentice'],
                ] as [$name, $city, $email]
            ) {
                DB::table('publishers')->insert([
                    'name' => $name,
                    'city' => $city,
                    'email' => $email.'@example.test',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $books = [
                ['Laskar Pelangi', '9789793062792', 1, 1, 1, 2005, 8],
                ['Atomic Habits', '9780735211292', 2, 2, 2, 2018, 6],
                ['Clean Code', '9780132350884', 3, 3, 3, 2008, 5],
                ['The Psychology of Money', '9780857197689', 2, 4, 2, 2020, 7],
                ['Sapiens', '9780062316097', 5, 5, 2, 2015, 4],
                ['Filosofi Kopi', '9789799625701', 1, 6, 1, 2006, 6],
                ['Deep Work', '9781455586691', 2, 7, 2, 2016, 5],
                ['The Midnight Library', '9780525559474', 1, 8, 2, 2020, 4],
            ];
            foreach ($books as [$title, $isbn, $category, $author, $publisher, $year, $stock]) {
                DB::table('books')->insert([
                    'title' => $title,
                    'isbn' => $isbn,
                    'categories_id' => $category,
                    'author_id' => $author,
                    'author' => $authors[$author - 1][0],
                    'publisher_id' => $publisher,
                    'published_year' => $year,
                    'total_stock' => $stock,
                    'shelf' => 'A-'.str_pad((string) $author, 2, '0', STR_PAD_LEFT),
                    'description' => 'myBook collection: '.$title.'.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            foreach (
                [[3, 1, -3, 11, null], [4, 3, -17, -3, null], [5, 2, -10, 4, -1]] as [$member, $book, $borrow, $due, $returned]
            ) {
                DB::table('loans')->insert([
                    'users_id' => $member,
                    'books_id' => $book,
                    'librarian_id' => 2,
                    'borrowing_method' => 'walk_in',
                    'borrow_date' => today()->addDays($borrow),
                    'due_date' => today()->addDays($due),
                    'return_date' => $returned === null ? null : today()->addDays($returned),
                    'status' => $returned === null ? 'borrowed' : 'returned',
                    'notes' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            foreach ([[6, 4, 'pending'], [7, 5, 'ready_for_pickup']] as [$member, $book, $status]) {
                DB::table('reservations')->insert([
                    'users_id' => $member,
                    'books_id' => $book,
                    'status' => $status,
                    'reserved_on' => today(),
                    'pickup_deadline' => $status === 'ready_for_pickup' ? now()->addDays(3) : null,
                    'notes' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
