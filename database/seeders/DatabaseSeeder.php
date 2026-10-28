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
            ['name' => 'Fiction', 'description' => 'Novels and short story collections by local and international authors.'],
            ['name' => 'Self Improvement', 'description' => 'Books on habits, productivity, and personal development.'],
            ['name' => 'History', 'description' => 'Historical accounts of nations, civilizations, and modern societies.'],
            ['name' => 'Science', 'description' => 'Popular science books covering physics, astronomy, and biology.'],
            ['name' => 'Computer Science', 'description' => 'Books on programming, algorithms, and software engineering.'],
        ])->mapWithKeys(fn (array $category) => [$category['name'] => Category::create($category)]);

        $books = collect([
            ['Laskar Pelangi', 'Andrea Hirata', 'Fiction', 5, 'The story of ten Belitung schoolchildren and their dedicated teachers.'],
            ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Fiction', 3, 'First novel of the Buru Quartet depicting life during colonial era.'],
            ['Laut Bercerita', 'Leila S. Chudori', 'Fiction', 2, 'A poignant story about student activists who went missing in the late 1990s.'],
            ['Cantik Itu Luka', 'Eka Kurniawan', 'Fiction', 1, 'An epic family saga set in the fictional coastal town of Halimunda.'],
            ['Filosofi Teras', 'Henry Manampiring', 'Self Improvement', 4, 'An introduction to ancient Stoic philosophy adapted for modern life.'],
            ['Atomic Habits', 'James Clear', 'Self Improvement', 3, 'An easy and proven way to build good habits and break bad ones.'],
            ['Sapiens', 'Yuval Noah Harari', 'History', 2, 'A narrative exploration of the history of humankind from early ancestors to the modern era.'],
            ['Sejarah Indonesia Modern', 'M.C. Ricklefs', 'History', 2, 'A comprehensive history of Indonesia from the arrival of Islam to the present.'],
            ['Cosmos', 'Carl Sagan', 'Science', 2, 'A journey exploring the universe, civilization, and our place in the cosmos.'],
            ['A Brief History of Time', 'Stephen Hawking', 'Science', 1, 'A landmark exploration of cosmology, black holes, and the origin of the universe.'],
            ['Clean Code', 'Robert C. Martin', 'Computer Science', 3, 'A handbook of agile software craftsmanship and best practices for writing clean code.'],
            ['Introduction to Algorithms', 'Thomas H. Cormen et al.', 'Computer Science', 2, 'The standard comprehensive textbook on computer algorithms and data structures.'],
            ['The Pragmatic Programmer', 'David Thomas & Andrew Hunt', 'Computer Science', 2, 'Practical advice and core habits for professional software developers.'],
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
