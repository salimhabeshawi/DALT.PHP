<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DALT.PHP</title>
    <style>
        :root {
            --bg-color: #040404;
            --bg-glow: rgba(74, 222, 128, 0.14);
            --surface-color: rgba(12, 12, 12, 0.9);
            --surface-strong: rgba(18, 18, 18, 0.98);
            --border-color: rgba(255, 255, 255, 0.1);
            --border-strong: rgba(74, 222, 128, 0.32);
            --text-main: #f5f7f5;
            --text-muted: #a6ada6;
            --accent: #4ade80;
            --accent-strong: #86efac;
            --shadow: 0 28px 80px rgba(0, 0, 0, 0.42);
        }

        * {
            box-sizing: border-box;
        }

        html {
            color-scheme: dark;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background:
                radial-gradient(circle at top, var(--bg-glow), transparent 34rem),
                linear-gradient(180deg, #090909 0%, var(--bg-color) 48%, #050505 100%);
            color: var(--text-main);
            line-height: 1.5;
        }

        a {
            color: inherit;
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: min(100%, 72rem);
            margin: 0 auto;
            padding: 4rem 2rem 3rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .hero {
            margin-bottom: 2rem;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding: 0.45rem 0.8rem;
            border: 1px solid var(--border-strong);
            border-radius: 999px;
            background: rgba(74, 222, 128, 0.08);
            color: var(--accent-strong);
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 0.75rem;
            font-size: clamp(2.6rem, 7vw, 4.8rem);
            line-height: 0.98;
            letter-spacing: -0.05em;
        }

        .subtitle {
            margin: 0;
            max-width: 46rem;
            font-size: 1.08rem;
            color: var(--text-muted);
        }

        .grid-links {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
            margin-bottom: 2rem;
        }

        .link-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.72rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.02);
            color: var(--text-main);
            text-decoration: none;
            transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
        }

        .link-pill:hover {
            border-color: var(--border-strong);
            background: rgba(74, 222, 128, 0.08);
            transform: translateY(-1px);
        }

        .main-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.5rem;
        }

        .panel {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 1.6rem;
            border: 1px solid var(--border-color);
            border-radius: 24px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01)),
                var(--surface-color);
            box-shadow: var(--shadow);
            backdrop-filter: blur(12px);
        }

        h2 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--text-main);
        }

        p {
            margin: 0;
            font-size: 0.97rem;
            color: var(--text-muted);
        }

        .terminal {
            margin: 0;
            padding: 0.9rem 1rem;
            border: 1px solid rgba(74, 222, 128, 0.22);
            border-radius: 14px;
            background: #020402;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.88rem;
            color: var(--accent);
            overflow-x: auto;
        }

        .terminal::before {
            content: "$ ";
            color: #6b7280;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: auto;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.72rem 1.05rem;
            border-radius: 12px;
            border: 1px solid transparent;
            font-size: 0.92rem;
            font-weight: 600;
            text-decoration: none;
            transition: border-color 0.2s ease, background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: var(--accent);
            color: #031407;
            border-color: var(--accent);
        }

        .btn-primary:hover {
            background: var(--accent-strong);
            border-color: var(--accent-strong);
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.02);
            color: var(--text-main);
            border-color: var(--border-color);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: var(--border-strong);
        }

        .site-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0));
        }

        .footer-inner {
            width: min(100%, 72rem);
            margin: 0 auto;
            padding: 1.75rem 2rem 1.5rem;
        }

        .footer-top {
            display: grid;
            grid-template-columns: minmax(0, 2fr) repeat(3, minmax(0, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .footer-brand h3,
        .footer-group h4 {
            margin: 0 0 0.6rem;
            font-size: 0.84rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-main);
        }

        .footer-brand p {
            max-width: 32rem;
            line-height: 1.65;
        }

        .footer-group {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .footer-link {
            width: fit-content;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .footer-link:hover {
            color: var(--accent-strong);
        }

        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer-copy,
        .footer-meta {
            font-size: 0.85rem;
        }

        @media (max-width: 900px) {
            .main-grid,
            .footer-top {
                grid-template-columns: 1fr;
            }

            .container {
                padding-top: 3rem;
            }
        }

        @media (max-width: 640px) {
            .container,
            .footer-inner {
                padding-left: 1.25rem;
                padding-right: 1.25rem;
            }

            h1 {
                font-size: 2.5rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .panel {
                padding: 1.25rem;
                border-radius: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="container">
            <section class="hero" aria-label="Welcome">
                <div class="eyebrow">Transparent PHP Learning Framework</div>
                <h1>DALT.PHP is ready.</h1>
                <p class="subtitle">A transparent PHP framework designed to teach backend development concepts through readable, focused code.</p>
            </section>

            <div class="grid-links">
                <a href="https://dalt.ibnuafdel.com/docs" class="link-pill" target="_blank" rel="noopener noreferrer">Documentation</a>
                <a href="https://t.me/daltphp" class="link-pill" target="_blank" rel="noopener noreferrer">Telegram Community</a>
                <a href="https://github.com/ibnu-Afdel/dALT.PHP" class="link-pill" target="_blank" rel="noopener noreferrer">GitHub</a>
            </div>

            <div class="main-grid">
                <?php $platformInstalled = function_exists('base_path') && is_dir(base_path('.dalt')); ?>
                <?php if ($platformInstalled): ?>
                <div class="panel">
                    <h2>Guided Learning</h2>
                    <p>DALT includes built-in interactive lessons. Dive in to grasp backend fundamentals directly within the framework.</p>
                    <div class="actions">
                        <a class="btn btn-primary" href="/learn/start">Start Learning</a>
                        <a class="btn btn-outline" href="/learn">Course Dashboard</a>
                    </div>
                </div>

                <div class="panel">
                    <h2>Start Building</h2>
                    <p>Prefer a clean slate? Remove the learning modules to get a minimal, barebones framework core:</p>
                    <div class="terminal">php artisan platform:remove</div>
                    <p>Then, learn by building. Explore the docs or follow these guides to master the framework flow.</p>
                    <div class="actions">
                        <a class="btn btn-outline" href="https://dalt.ibnuafdel.com/docs/guides/building-a-blog" target="_blank" rel="noopener noreferrer">Build a Blog</a>
                        <a class="btn btn-outline" href="https://dalt.ibnuafdel.com/docs/guides/building-an-api" target="_blank" rel="noopener noreferrer">Build an API</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="panel">
                    <h2>Build Your Application</h2>
                    <p>The framework is clean and ready. Learn by building practical applications. Explore the docs or follow our step-by-step guides to master the framework flow.</p>
                    <div class="actions">
                        <a class="btn btn-outline" href="https://dalt.ibnuafdel.com/docs/guides/building-a-blog" target="_blank" rel="noopener noreferrer">Build a Blog</a>
                        <a class="btn btn-outline" href="https://dalt.ibnuafdel.com/docs/guides/building-an-api" target="_blank" rel="noopener noreferrer">Build an API</a>
                    </div>
                </div>
                <div class="panel">
                    <h2>Guided Learning Removed</h2>
                    <p>The learning modules are not installed in this project.</p>
                    <div class="terminal">php artisan platform:status</div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <footer class="site-footer">
            <div class="footer-inner">
                <div class="footer-top">
                    <section class="footer-brand" aria-label="About DALT.PHP">
                        <h3>DALT.PHP</h3>
                        <p>
                            A transparent backend learning framework where routing, sessions, SQL,
                            middleware, and validation stay readable so you can understand each concept
                            by building real features.
                        </p>
                    </section>

                    <section class="footer-group" aria-label="Learn">
                        <h4>Learn</h4>
                        <a class="footer-link" href="/">Home</a>
                        <a class="footer-link" href="/learn/start">Start Learning</a>
                        <a class="footer-link" href="/learn">Course Dashboard</a>
                    </section>

                    <section class="footer-group" aria-label="Resources">
                        <h4>Resources</h4>
                        <a class="footer-link" href="https://dalt.ibnuafdel.com/docs" target="_blank" rel="noopener noreferrer">Documentation</a>
                        <a class="footer-link" href="https://dalt.ibnuafdel.com/docs/guides/building-a-blog" target="_blank" rel="noopener noreferrer">Build a Blog</a>
                        <a class="footer-link" href="https://dalt.ibnuafdel.com/docs/guides/building-an-api" target="_blank" rel="noopener noreferrer">Build an API</a>
                    </section>

                    <section class="footer-group" aria-label="Community">
                        <h4>Community</h4>
                        <a class="footer-link" href="https://github.com/ibnu-Afdel/dALT.PHP" target="_blank" rel="noopener noreferrer">GitHub</a>
                        <a class="footer-link" href="https://t.me/daltphp" target="_blank" rel="noopener noreferrer">Telegram</a>
                        <a class="footer-link" href="https://dalt.ibnuafdel.com/docs" target="_blank" rel="noopener noreferrer">Release Notes</a>
                    </section>
                </div>

                <div class="footer-bottom">
                    <p class="footer-copy">&copy; <?= date('Y') ?> DALT.PHP. Built to learn by building.</p>
                    <p class="footer-meta">Focused on clarity over magic, so backend concepts stay practical and visible.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
