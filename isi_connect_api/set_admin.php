<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::firstOrNew(['email' => 'multibrainmusic1@gmail.com']);
$user->name = $user->name ?? 'Admin Account';
$user->password = \Hash::make('password');
$user->promotion_year = 2026;
$user->role = 'admin';
$user->save();

if (!$user->profile) {
    \App\Models\Profile::create([
        'user_id' => $user->id,
        'bio' => 'Administrateur de la plateforme',
        'is_featured_in_showcase' => true
    ]);
}

echo "Admin account (multibrainmusic1@gmail.com) successfully configured!\n";
