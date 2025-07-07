<?php
$typed = 'password123';
$hash = '$2y$10$6F71M7HyW3KujJKM1HyUCeGzNRkk8jLG0RJgODc1KqllHOO1lALw6';

if (password_verify($typed, $hash)) {
    echo "✅ MATCH!";
} else {
    echo "❌ NO MATCH!";
}
