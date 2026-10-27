<?php

namespace Database\Seeders;

use App\Enums\BorrowingMethod;
use App\Enums\LoanStatus;
use App\Enums\ReservationStatus;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin myBook',
            'email' => 'admin@mybook.test',
        ]);

        $librarian = User::factory()->librarian()->create([
            'name' => 'Siti Rahmawati',
            'email' => 'siti@mybook.test',
        ]);

        User::factory()->librarian()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@mybook.test',
        ]);

        $rina = User::factory()->create(['name' => 'Rina Kusuma', 'email' => 'rina@mybook.test']);
        $dimas = User::factory()->create(['name' => 'Dimas Pratama', 'email' => 'dimas@mybook.test']);
        $ayu = User::factory()->create(['name' => 'Ayu Lestari', 'email' => 'ayu@mybook.test']);
        User::factory()->inactive()->create(['name' => 'Fajar Nugroho', 'email' => 'fajar@mybook.test']);

        $categories = collect([
            ['name' => 'Fiksi', 'description' => 'Novel dan kumpulan cerita karya penulis Indonesia maupun terjemahan.'],
            ['name' => 'Pengembangan Diri', 'description' => 'Buku tentang kebiasaan, produktivitas, dan cara berpikir.'],
            ['name' => 'Sejarah', 'description' => 'Buku sejarah Indonesia dan dunia.'],
            ['name' => 'Sains', 'description' => 'Buku populer tentang fisika, astronomi, dan biologi.'],
            ['name' => 'Komputer', 'description' => 'Buku pemrograman, algoritma, dan rekayasa perangkat lunak.'],
        ])->mapWithKeys(fn (array $category) => [$category['name'] => Category::create($category)]);

        $books = collect([
            ['Laskar Pelangi', 'Andrea Hirata', 'Fiksi', 5, 'Kisah sepuluh anak Belitung dan sekolah Muhammadiyah mereka.'],
            ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Fiksi', 3, 'Novel pertama Tetralogi Buru tentang Minke di masa kolonial.'],
            ['Laut Bercerita', 'Leila S. Chudori', 'Fiksi', 2, 'Novel tentang aktivis mahasiswa yang hilang pada akhir 1990-an.'],
            ['Cantik Itu Luka', 'Eka Kurniawan', 'Fiksi', 1, 'Novel tentang keluarga Dewi Ayu di kota fiktif Halimunda.'],
            ['Filosofi Teras', 'Henry Manampiring', 'Pengembangan Diri', 4, 'Pengantar filsafat Stoa untuk kehidupan sehari-hari.'],
            ['Atomic Habits', 'James Clear', 'Pengembangan Diri', 3, 'Cara membangun kebiasaan baik lewat perubahan kecil.'],
            ['Sapiens', 'Yuval Noah Harari', 'Sejarah', 2, 'Riwayat singkat umat manusia dari zaman batu hingga modern.'],
            ['Sejarah Indonesia Modern', 'M.C. Ricklefs', 'Sejarah', 2, 'Sejarah Indonesia sejak masuknya Islam hingga masa kini.'],
            ['Cosmos', 'Carl Sagan', 'Sains', 2, 'Perjalanan memahami alam semesta dan tempat manusia di dalamnya.'],
            ['A Brief History of Time', 'Stephen Hawking', 'Sains', 1, 'Penjelasan populer tentang lubang hitam, waktu, dan asal-usul alam semesta.'],
            ['Clean Code', 'Robert C. Martin', 'Komputer', 3, 'Prinsip menulis kode yang mudah dibaca dan dirawat.'],
            ['Introduction to Algorithms', 'Thomas H. Cormen dkk.', 'Komputer', 2, 'Buku teks algoritma dan struktur data.'],
            ['The Pragmatic Programmer', 'David Thomas & Andrew Hunt', 'Komputer', 2, 'Kebiasaan dan praktik kerja programmer profesional.'],
        ])->mapWithKeys(fn (array $book) => [$book[0] => Book::create([
            'title' => $book[0],
            'author' => $book[1],
            'isbn' => fake()->unique()->isbn13(),
            'description' => $book[4],
            'total_stock' => $book[3],
            'categories_id' => $categories[$book[2]]->id,
        ])]);

        Reservation::create([
            'status' => ReservationStatus::Pending,
            'users_id' => $rina->id,
            'books_id' => $books['Bumi Manusia']->id,
        ]);

        Reservation::create([
            'status' => ReservationStatus::ReadyForPickup,
            'pickup_deadline' => now()->addDays(2),
            'processed_at' => now()->subHours(3),
            'users_id' => $dimas->id,
            'books_id' => $books['Filosofi Teras']->id,
            'processed_by' => $librarian->id,
        ]);

        Reservation::create([
            'status' => ReservationStatus::ReadyForPickup,
            'pickup_deadline' => now()->subDay(),
            'processed_at' => now()->subDays(4),
            'users_id' => $ayu->id,
            'books_id' => $books['Cosmos']->id,
            'processed_by' => $librarian->id,
        ]);

        Reservation::create([
            'status' => ReservationStatus::Rejected,
            'processed_at' => now()->subDays(5),
            'users_id' => $rina->id,
            'books_id' => $books['Cantik Itu Luka']->id,
            'processed_by' => $librarian->id,
        ]);

        Reservation::create([
            'status' => ReservationStatus::Cancelled,
            'users_id' => $dimas->id,
            'books_id' => $books['Sapiens']->id,
        ]);

        $completed = Reservation::create([
            'status' => ReservationStatus::Completed,
            'pickup_deadline' => now()->subDays(8),
            'processed_at' => now()->subDays(10),
            'users_id' => $ayu->id,
            'books_id' => $books['Clean Code']->id,
            'processed_by' => $librarian->id,
        ]);

        Loan::create([
            'borrowing_method' => BorrowingMethod::Reservation,
            'borrow_date' => now()->subDays(9),
            'due_date' => now()->addDays(5),
            'status' => LoanStatus::Borrowed,
            'reservations_id' => $completed->id,
            'users_id' => $ayu->id,
            'books_id' => $books['Clean Code']->id,
            'librarian_id' => $librarian->id,
        ]);

        Loan::create([
            'borrowing_method' => BorrowingMethod::WalkIn,
            'borrow_date' => now()->subDays(3),
            'due_date' => now()->addDays(11),
            'status' => LoanStatus::Borrowed,
            'users_id' => $rina->id,
            'books_id' => $books['Laskar Pelangi']->id,
            'librarian_id' => $librarian->id,
        ]);

        Loan::create([
            'borrowing_method' => BorrowingMethod::WalkIn,
            'borrow_date' => now()->subDays(20),
            'due_date' => now()->subDays(6),
            'status' => LoanStatus::Borrowed,
            'users_id' => $dimas->id,
            'books_id' => $books['A Brief History of Time']->id,
            'librarian_id' => $librarian->id,
        ]);

        Loan::create([
            'borrowing_method' => BorrowingMethod::WalkIn,
            'borrow_date' => now()->subDays(30),
            'due_date' => now()->subDays(16),
            'return_date' => now()->subDays(18),
            'status' => LoanStatus::Returned,
            'users_id' => $rina->id,
            'books_id' => $books['Atomic Habits']->id,
            'librarian_id' => $librarian->id,
        ]);
    }
}
