<?php
session_start();

/* --- include config yang fleksibel (di /api atau ../api) --- */
$config1 = __DIR__ . '/api/config.php';
$config2 = __DIR__ . '/../api/config.php';
if (file_exists($config1)) {
    require $config1;
} elseif (file_exists($config2)) {
    require $config2;
} else {
    die('Konfigurasi DB (api/config.php) tidak ditemukan.');
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // trim untuk cegah spasi tak sengaja
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // validasi sederhana
    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi.";
    } else {
        // query user by username
        $sql  = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = "Kesalahan server: " . $conn->error;
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // ambil nilai dari DB
                $stored = $user['password_hash'] ?? '';

                // Jika sudah hash (bcrypt/argon2) -> pakai password_verify
                // Kalau masih plain text -> bandingkan langsung (hash_equals)
                $looks_hashed = preg_match('/^\$2y\$\d{2}\$|^\$argon2(id|i|d)/', $stored) === 1;
                $valid = $looks_hashed ? password_verify($password, $stored) : hash_equals($stored, $password);

                if ($valid) {
                    // set session
                    $_SESSION['user_id']  = (int)$user['id'];
                    $_SESSION['username'] = $user['username'];
                    if (isset($user['role'])) $_SESSION['role'] = $user['role'];
                    if (isset($user['nama'])) $_SESSION['nama'] = $user['nama'];

                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Password salah!";
                }
            } else {
                $error = "Username tidak ditemukan!";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login - Koperasi Santri</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-400 to-emerald-600 font-sans">
    <div class="w-full max-w-md p-8 bg-white/90 backdrop-blur-md rounded-2xl shadow-xl">
        <h2 class="text-3xl font-extrabold text-center text-emerald-700 mb-6">Login</h2>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-700 text-sm text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
            <div>
                <label class="block text-gray-700 mb-2 font-medium">Username</label>
                <input type="text" name="username" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                       value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
            </div>
            <div>
                <label class="block text-gray-700 mb-2 font-medium">Password</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <button type="submit"
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-green-400 to-emerald-600 text-white font-semibold hover:scale-105 transform transition duration-300 shadow-lg">
                Login
            </button>
        </form>

        <p class="mt-6 text-center text-gray-600 text-sm">&copy; <?= date("Y"); ?> Koperasi Santri</p>
    </div>
</body>
</html>
