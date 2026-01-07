<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Agro Management — Settings</title>
  <style>
    /* ----- Base ----- */
    :root{
      --green-700:#1b5e20; --green-600:#2e7d32; --green-400:#66bb6a; --muted:#6b7280;
      --card-bg:#ffffff; --bg:#f5f9f6; --accent:#2e7d32;
      --radius:12px; --glass: rgba(255,255,255,0.6);
      font-family: Inter, Poppins, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
    }
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);color:#0f172a;min-height:100vh}

    /* ----- Layout ----- */
    .app{display:flex;min-height:100vh}
    .sidebar{width:260px;background:linear-gradient(180deg,var(--green-700),var(--green-600));color:#fff;padding:28px 18px;display:flex;flex-direction:column}
    .brand{font-weight:700;font-size:20px;letter-spacing:0.4px;margin-bottom:18px}
    .nav{display:flex;flex-direction:column;gap:6px}
    .nav a, .nav button{background:transparent;border:0;color:inherit;text-align:left;padding:12px;border-radius:10px;cursor:pointer;font-size:15px;text-decoration:none;display:block}
    .nav a:hover, .nav button:hover{background:rgba(255,255,255,0.06)}
    .nav a.active, .nav button.active{background:rgba(255,255,255,0.12)}
    .logout-btn{color:#ff6b6b}
    .logout-btn:hover{background:rgba(255,99,71,0.1); color:#ff4757}
    .sidebar .spacer{flex:1}
    .sidebar small{opacity:0.9}

    .main{flex:1;padding:28px}
    header.main-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
    .search{display:flex;gap:12px;align-items:center}
    .search input{padding:10px 12px;border-radius:10px;border:1px solid #e6f0ea;background:white;min-width:300px}
    .btn{background:var(--accent);color:white;padding:10px 16px;border-radius:10px;border:0;cursor:pointer}
    .btn.ghost{background:transparent;color:var(--green-700);border:1px solid rgba(16,185,129,0.12)}

    /* ----- Cards & grid ----- */
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}
    .card{background:var(--card-bg);padding:18px;border-radius:var(--radius);box-shadow:0 6px 18px rgba(12,17,15,0.06)}

    /* ----- Forms ----- */
    .form-row{display:flex;gap:12px}
    .field{flex:1}
    .field label{display:block;margin-bottom:4px;font-size:14px;color:var(--muted)}
    .field input, .field select{width:100%;padding:10px;border-radius:8px;border:1px solid #e6f0ea}
  </style>
</head>

<body>
  <div class="app">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="brand">Agro Management</div>

      <nav class="nav">
        <a href="dashboard.html">Dashboard</a>
        <a href="products.html">Products</a>
        <a href="customers.html">Customers</a>
        <a href="invoices.html">Invoices</a>
        <a href="stock.html">Stock</a>
        <a href="reports.html">Reports</a>
        <a href="expenses.html">Expenses</a>
        <a href="settings.html" class="active">Settings</a>
        <button id="logoutBtn" class="logout-btn">Logout</button>
      </nav>

      <div class="spacer"></div>
      <small>Logged in as <strong>Owner</strong></small>
    </aside>

    <!-- MAIN -->
    <main class="main">
      <header class="main-header">
        <div>
          <h1 style="margin:0;font-size:20px">Settings</h1>
          <div class="small muted">Configure your preferences</div>
        </div>
      </header>

      <!-- SETTINGS PAGE -->
      <div class="card" style="max-width:680px">
        <div class="form-row">
          <div class="field"><label>Company Name</label><input id="companyName"/></div>
          <div class="field"><label>Invoice Prefix</label><input id="invoicePrefix"/></div>
        </div>
        <div style="margin-top:12px"><button class="btn" id="saveSettings">Save Settings</button></div>
      </div>
    </main>
  </div>

  <script>
// ===== Helpers =====
function $(s){return document.querySelector(s)}

// save settings
$('#saveSettings').addEventListener('click',()=>{ alert('Settings saved (demo)'); });

// ===== Logout =====
$('#logoutBtn').addEventListener('click',()=>{
  window.location.href = 'landing.html';
});
  </script>
</body>
</html>