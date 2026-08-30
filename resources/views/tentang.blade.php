<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami — KampusLMS Kelompok 05</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            --card-bg: rgba(30, 41, 59, 0.7);
            --card-border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.25);
            --accent: #38bdf8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            min-height: 100vh;
            background: var(--bg-gradient);
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow Effects */
        .glow {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            z-index: 0;
        }
        .glow-1 {
            top: -100px;
            left: -100px;
            background: rgba(99, 102, 241, 0.3);
        }
        .glow-2 {
            bottom: -100px;
            right: -100px;
            background: rgba(56, 189, 248, 0.2);
        }

        .container {
            width: 100%;
            max-width: 800px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary-glow);
            border: 1px solid rgba(99, 102, 241, 0.4);
            color: #c7d2fe;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            margin-bottom: 1.25rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        h1 {
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 0.75rem;
            background: linear-gradient(to right, #ffffff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 1.05rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        .meta-card {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 1.25rem;
            transition: transform 0.2s, border-color 0.2s;
        }

        .meta-card:hover {
            transform: translateY(-2px);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .meta-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 0.35rem;
        }

        .meta-value {
            font-size: 1.05rem;
            font-weight: 700;
            color: #f1f5f9;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #e2e8f0;
        }

        .member-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        .member-card {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s;
        }

        .member-card:hover {
            border-color: var(--primary);
            background: rgba(30, 41, 59, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px var(--primary-glow);
        }

        .avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .member-info h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #f8fafc;
            margin-bottom: 0.2rem;
        }

        .member-info p {
            font-size: 0.8rem;
            color: var(--accent);
            font-weight: 500;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--primary), #4f46e5);
            color: white;
            padding: 0.85rem 1.75rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
            box-shadow: 0 4px 15px var(--primary-glow);
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
            filter: brightness(1.1);
        }

        .footer {
            margin-top: 2.5rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>

    <div class="container">
        <div class="badge">
            ⚡ SI2514024 • Pemrograman Web
        </div>

        <h1>KampusLMS — Kelompok 05</h1>
        <p class="subtitle">
            Proyek pengembangan Learning Management System (LMS) modern berbasis <strong>Laravel 12</strong> untuk Semester Ganjil 2026/2027.
        </p>

        <div class="meta-grid">
            <div class="meta-card">
                <div class="meta-label">Mata Kuliah</div>
                <div class="meta-value">Pemrograman Web</div>
            </div>
            <div class="meta-card">
                <div class="meta-label">Kelompok</div>
                <div class="meta-value">Kelompok 05</div>
            </div>
            <div class="meta-card">
                <div class="meta-label">Tech Stack</div>
                <div class="meta-value">Laravel 12 & MySQL</div>
            </div>
        </div>

        <div class="section-title">
            👥 Anggota Kelompok
        </div>

        <div class="member-list">
            <div class="member-card">
                <div class="avatar">R</div>
                <div class="member-info">
                    <h3>Muhammad Rifa Al Rizqul Aulia</h3>
                    <p>Frontend Developer • 10241050 (@rifarizqul)</p>
                </div>
            </div>
            <div class="member-card">
                <div class="avatar">N</div>
                <div class="member-info">
                    <h3>Nova Reskianti</h3>
                    <p>Frontend Developer • 10241058 (@Novares06)</p>
                </div>
            </div>
            <div class="member-card">
                <div class="avatar">Z</div>
                <div class="member-info">
                    <h3>Muhammad Zaldy Syah Firaz</h3>
                    <p>Backend Developer • 10241054 (@muhammadzaldysyahfiraz)</p>
                </div>
            </div>
            <div class="member-card">
                <div class="avatar">Y</div>
                <div class="member-info">
                    <h3>Muhammad Yuspa Ardiansyah</h3>
                    <p>Backend Developer • 10241052 (@ardiansyahyus24)</p>
                </div>
            </div>
            <div class="member-card">
                <div class="avatar">F</div>
                <div class="member-info">
                    <h3>Muhammad Farin Murtadho Syafiq</h3>
                    <p>Database Engineer • 10241046 (@muhammadfarin18)</p>
                </div>
            </div>
        </div>

        <div>
            <a href="{{ url('/') }}" class="btn-back">
                ← Kembali ke Beranda
            </a>
        </div>

        <div class="footer">
            Dibuat untuk memenuhi Tugas Praktikum Minggu 1 • Institut Teknologi Kalimantan (ITK)
        </div>
    </div>
</body>
</html>
