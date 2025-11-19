<style>
        :root {
            --setoran-page-bg: linear-gradient(180deg, #f8fafc 0%, #eef2ff 55%, #f0fdfa 100%);
            --setoran-card-bg: rgba(255,255,255,0.95);
            --setoran-card-border: rgba(15,23,42,0.08);
            --setoran-text: #0f172a;
            --setoran-muted: #64748b;
            --setoran-hero-gradient: linear-gradient(135deg, #1d4ed8, #0ea5e9 55%, #14b8a6);
            --setoran-pill-border: rgba(255,255,255,0.5);
        }

        html.dark {
            --setoran-page-bg: radial-gradient(circle at 10% 20%, rgba(15,118,210,0.3), rgba(2,6,23,0.95));
            --setoran-card-bg: rgba(2,6,23,0.85);
            --setoran-card-border: rgba(148,163,184,0.35);
            --setoran-text: #e2e8f0;
            --setoran-muted: rgba(226,232,240,0.65);
            --setoran-hero-gradient: linear-gradient(140deg, #0f172a, #1d4ed8 45%, #0ea5e9);
            --setoran-pill-border: rgba(255,255,255,0.25);
        }

        .setoran-page {
            position: relative;
            min-height: 100vh;
            padding: 1rem clamp(0.85rem, 3vw, 2rem) 2.5rem;
            background: var(--setoran-page-bg);
            overflow-x: hidden;
        }

        .setoran-wrapper {
            position: relative;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            color: var(--setoran-text);
            display: flex;
            flex-direction: column;
            gap: clamp(1.25rem, 2.5vw, 1.75rem);
            overflow-x: hidden;
        }

        .setoran-hero {
            width: 100%;
            position: relative;
            border-radius: 2rem;
            padding: 2.5rem;
            color: #fff;
            background: var(--setoran-hero-gradient);
            overflow: hidden;
            box-shadow: 0 35px 80px rgba(15,23,42,0.35);
        }

        .setoran-hero::after,
        .setoran-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .setoran-hero::before {
            background: radial-gradient(circle at top left, rgba(255,255,255,0.55), transparent 60%);
            opacity: 0.35;
        }

        .setoran-hero::after {
            background: radial-gradient(circle at bottom right, rgba(255,255,255,0.2), transparent 65%);
            opacity: 0.25;
        }

        .setoran-hero__content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        @media (min-width: 1024px) {
            .setoran-hero__content {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .setoran-eyebrow {
            letter-spacing: 0.45em;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 600;
            opacity: 0.75;
        }

        .setoran-pill {
            padding: 0.5rem 1rem;
            border-radius: 999px;
            border: 1px solid var(--setoran-pill-border);
            background: rgba(255,255,255,0.15);
        }

        .setoran-hero__form {
            width: 100%;
            max-width: 320px;
            border-radius: 1.5rem;
            background: rgba(15,23,42,0.35);
            padding: 1.25rem;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.2);
        }

        .setoran-select {
            width: 100%;
            border-radius: 1rem;
            border: 1px solid rgba(255,255,255,0.35);
            background-color: rgba(255,255,255,0.2);
            color: #fff;
            padding: 0.85rem 1rem;
            font-weight: 600;
        }

        .setoran-select option {
            color: #0f172a;
            background-color: #f8fafc;
            font-weight: 500;
        }

        html.dark .setoran-select option {
            color: #e2e8f0;
            background-color: #0f172a;
        }

        .setoran-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 999px;
            padding: 0.65rem 1.25rem;
            font-weight: 600;
            background: rgba(255,255,255,0.2);
            color: #fff;
        }

        .setoran-button--action {
            background: linear-gradient(135deg, #0ea5e9, #2563eb, #7c3aed);
            color: #fff;
            box-shadow: 0 15px 35px rgba(79,70,229,0.35);
        }

        .setoran-stats {
            width: 100%;
            display: grid;
            gap: clamp(1.1rem, 2vw, 1.5rem);
        }

        .setoran-stat-card {
            border-radius: 1.5rem;
            padding: 1.5rem;
            color: #fff;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            box-shadow: 0 25px 60px rgba(14,165,233,0.35);
        }

        .setoran-stat-card--secondary {
            background: linear-gradient(135deg, #f97316, #ea580c);
        }

        .setoran-stat-card--accent {
            background: linear-gradient(135deg, #22c55e, #15803d);
        }

        .setoran-stat-label {
            text-transform: uppercase;
            letter-spacing: 0.35em;
            font-size: 0.7rem;
        }

        .setoran-stat-value {
            font-size: 2rem;
            font-weight: 700;
        }

        .setoran-card {
            width: 100%;
            border-radius: 1.75rem;
            border: 1px solid var(--setoran-card-border);
            background: var(--setoran-card-bg);
            padding: 1.75rem;
            box-shadow: 0 25px 60px rgba(15,23,42,0.08);
        }

        .santri-card {
            width: 100%;
            border-radius: 1.75rem;
            border: 1px solid rgba(148,163,184,0.25);
            padding: 1.5rem;
            background: rgba(255,255,255,0.92);
            box-shadow: 0 20px 55px rgba(15,23,42,0.12);
        }

        html.dark .santri-card {
            background: rgba(15,23,42,0.75);
            border-color: rgba(148,163,184,0.3);
        }

        .santri-chip {
            font-size: 0.75rem;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .chip-danger {
            background: rgba(248,113,113,0.15);
            color: #b91c1c;
        }

        .chip-warning {
            background: rgba(251,191,36,0.18);
            color: #92400e;
        }

        .setoran-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-weight: 600;
            color: #2563eb;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border-bottom: 1px solid rgba(15,23,42,0.08);
            padding: 0.85rem 0.5rem;
        }

        html.dark .table th,
        html.dark .table td {
            border-color: rgba(148,163,184,0.22);
        }

        .table th {
            font-size: 0.7rem;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--setoran-muted);
        }

        @media (max-width: 640px) {
            .setoran-page {
                padding: 0.85rem clamp(0.65rem, 4vw, 1rem) 1.75rem;
            }

            .setoran-wrapper {
                padding: 0;
            }

            .setoran-hero {
                border-radius: 1.5rem;
                padding: 1.75rem;
            }

            .setoran-hero__content {
                gap: 1.25rem;
            }

            .setoran-hero__form {
                border-radius: 1rem;
                padding: 1rem;
            }

            .setoran-card {
                padding: 1.25rem;
                border-radius: 1.25rem;
            }

            .santri-card {
                padding: 1.25rem;
                border-radius: 1.25rem;
            }

            .setoran-stats {
                grid-template-columns: 1fr;
            }

            .setoran-stat-card {
                padding: 1.25rem;
            }

            .table th,
            .table td {
                padding-left: 0.25rem;
                padding-right: 0.25rem;
            }
        }
    </style>
