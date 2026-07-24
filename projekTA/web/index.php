<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      margin: 0;
      transition: background 0.3s, color 0.3s;
    }
    body.light-mode {
      background: linear-gradient(135deg, #e3f2fd, #bbdefb);
      color: #333;
    }
    body.dark-mode {
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      color: #fff;
    }
    .navbar {
      transition: background 0.3s;
    }
    body.light-mode .navbar {
      background: linear-gradient(90deg, #64b5f6, #42a5f5);
    }
    body.dark-mode .navbar {
      background: linear-gradient(90deg, #2a5298, #1e3c72);
    }
    .navbar-brand {
      font-weight: 700;
      color: #fff !important;
    }
    .dashboard-header {
      margin-top: 50px;
      text-align: center;
    }
    .dashboard-header h2 {
      font-weight: 700;
      font-size: 2rem;
    }
    .dashboard-header .welcome {
      margin-top: 10px;
      font-size: 1.1rem;
      opacity: 0.9;
    }
    .menu-container {
      margin-top: 50px;
    }
    .menu-card {
      border-radius: 15px;
      padding: 30px;
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s, color 0.3s;
      box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    body.light-mode .menu-card {
      background: #fff;
      color: #333;
    }
    body.dark-mode .menu-card {
      background: #2e3c50;
      color: #fff;
    }
    .menu-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    }
    .menu-card a {
      text-decoration: none;
      font-weight: 600;
      font-size: 1.1rem;
      transition: color 0.3s;
    }
    body.light-mode .menu-card a {
      color: #2a5298;
    }
    body.dark-mode .menu-card a {
      color: #90caf9;
    }
    .theme-toggle {
      cursor: pointer;
      border: none;
      background: transparent;
      color: #fff;
      font-size: 1.2rem;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="#">📊 Dashboard</a>
      <div class="d-flex align-items-center">
        <span class="me-3">Halo, <?= htmlentities($_SESSION['username']); ?></span>
        <button class="theme-toggle me-3" id="themeToggle">🌙</button>
        <a href="logout.php" class="btn btn-light btn-sm rounded-pill px-3">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Header -->
  <div class="dashboard-header">
    <h2>Selamat Datang di Dashboard</h2>
    <p class="welcome">Pilih menu di bawah untuk melanjutkan</p>
  </div>

  <!-- Menu -->
  <div class="container menu-container">
    <div class="row g-4 justify-content-center">
      <div class="col-md-3">
        <div class="menu-card">
          <a href="santri.php">👨‍🎓 Data Santri</a>
        </div>
      </div>
      <div class="col-md-3">
        <div class="menu-card">
          <a href="barang.php">📦 Barang</a>
        </div>
      </div>
      <div class="col-md-3">
        <div class="menu-card">
          <a href="pos.php">🛒 POS</a>
        </div>
      </div>
      <div class="col-md-3">
        <div class="menu-card">
          <a href="transaksi.php">💰 Transaksi</a>
        </div>
      </div>
      <div class="col-md-3">
        <div class="menu-card">
          <a href="register_card.php">💳 Register Card</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Dark Mode Script -->
  <script>
    const body = document.body;
    const toggleBtn = document.getElementById('themeToggle');

    // Cek preferensi user dari localStorage
    if (localStorage.getItem('theme') === 'dark') {
      body.classList.add('dark-mode');
      toggleBtn.textContent = '☀️';
    } else {
      body.classList.add('light-mode');
      toggleBtn.textContent = '🌙';
    }

    toggleBtn.addEventListener('click', () => {
      if (body.classList.contains('light-mode')) {
        body.classList.remove('light-mode');
        body.classList.add('dark-mode');
        toggleBtn.textContent = '☀️';
        localStorage.setItem('theme', 'dark');
      } else {
        body.classList.remove('dark-mode');
        body.classList.add('light-mode');
        toggleBtn.textContent = '🌙';
        localStorage.setItem('theme', 'light');
      }
    });
  </script>
</body>
</html>
