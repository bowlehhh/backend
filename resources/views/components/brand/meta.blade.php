<link rel="icon" type="image/svg+xml" href="{{ asset('branding/sdm-mark.svg') }}">
<link rel="shortcut icon" href="{{ asset('branding/sdm-mark.svg') }}">
<meta name="theme-color" content="#0a5a58">
<style>
    @media (max-width: 767px) {
        html,
        body {
            overflow-x: hidden;
        }

        body > div.h-screen.overflow-hidden,
        body > div.min-h-screen.overflow-hidden {
            height: auto !important;
            min-height: 100vh !important;
            overflow: visible !important;
        }

        body > div.h-screen.overflow-hidden > main,
        body > div.min-h-screen.overflow-hidden > main,
        body > div.h-screen.overflow-hidden > div > main,
        body > div.min-h-screen.overflow-hidden > div > main {
            height: auto !important;
            min-height: 0 !important;
            overflow: visible !important;
        }

        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
        }

        th,
        td {
            word-break: break-word;
        }

        img,
        svg,
        video,
        canvas {
            max-width: 100%;
            height: auto;
        }
    }
</style>
