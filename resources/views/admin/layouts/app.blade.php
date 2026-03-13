<!DOCTYPE html>
<html lang="tr" class="h-full" x-data="{ mobileSidebarOpen: false }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Yönetim Paneli') | {{ env('APP_NAME') }}</title>
    <link rel="apple-touch-icon" href="{{ $settings->favicon ?? asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ $settings->favicon ?? asset('favicon.ico') }}">
    <link rel="icon" type="image/svg+xml" href="{{ $settings->favicon ?? asset('favicon.ico') }}">
    <link rel="icon" type="image/webp" href="{{ $settings->favicon ?? asset('favicon.ico') }}">
    <link rel="icon" type="image/gif" href="{{ $settings->favicon ?? asset('favicon.ico') }}">
    <link rel="icon" type="image/jpg" href="{{ $settings->favicon ?? asset('favicon.ico') }}">
    <link rel="icon" type="image/jpeg" href="{{ $settings->favicon ?? asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/admin/assets/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
        referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.4/dist/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        white: '#fff',
                        surface: {
                            light: '#f7f7f7',
                            dark: '#0a0a0a'
                        }
                    }
                }
            }
        }
    </script>
    <script>
        window.AppConfig = Object.assign({}, window.AppConfig || {}, {
            files: {
                types: @json(config('files.types', []))
            }
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/not-a-toast@latest/dist/not-a-toast.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.4" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js" referrerpolicy="no-referrer"></script>

    @stack('head')
</head>

<body class="h-full min-h-screen bg-surface-light text-black dark:bg-surface-dark dark:text-white transition-colors">
    <div class="flex min-h-screen">
        @include('admin.parts.sidebar')
        <div class="flex-1 flex flex-col">
            @include('admin.parts.header')
            <main class="p-4 md:p-6 lg:p-8 my-20">
                @yield('content')
            </main>
            @include('admin.parts.footer')
        </div>
    </div>

    <script>
        (function initTheme() {
            try {
                const saved = localStorage.getItem('admin-theme');
                if (saved === 'dark' || (!saved && window.matchMedia && window.matchMedia(
                        '(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (_) {}
        })();

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            try {
                localStorage.setItem('admin-theme', isDark ? 'dark' : 'light');
            } catch (_) {}
            if (typeof window.applyLogoTheme === 'function') {
                window.applyLogoTheme();
            }
        }
    </script>

    <script src="/assets/admin/assets/app.js"></script>
    <script src="/assets/admin/assets/tinymce/tinymce.min.js"></script>
    <script>
        function initTiny(selector = 'textarea') {
            if (typeof tinymce === 'undefined') {
                console.warn('TinyMCE is not loaded');
                return;
            }

            tinymce.init({
                selector: selector,
                height: 300,
                menubar: false,
                language: 'tr',
                convert_urls: false,
                skin: 'tinymce-5',
                plugins: [
                    "advlist", "autolink", "lists", "link", "image", "charmap", "preview", "anchor",
                    "searchreplace", "visualblocks", "code", "fullscreen", "fontsize",
                    "insertdatetime", "media", "table"
                ],
                imagetools_cors_hosts: ['www.tinymce.com', 'codepen.io'],
                file_picker_types: 'file image media',
                toolbar1: "table | hr removeformat | fontsize | subscript superscript | charmap emoticons | print fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | fontselect fontsizeselect | code",
                toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link | insertdatetime preview | forecolor backcolor | image",
                entity_encoding: "raw",
                file_picker_callback: function(cb, value, meta) {
                    var input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');

                    input.onchange = function() {
                        var file = this.files[0];

                        var reader = new FileReader();
                        reader.onload = function() {
                            var id = 'blobid' + (new Date()).getTime();
                            var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                            var base64 = reader.result.split(',')[1];
                            var blobInfo = blobCache.create(id, file, base64);
                            blobCache.add(blobInfo);
                            console.log(file)
                            if (file.size > 25689400) {
                                alert('25MB üzerinde fotoğraf yüklenemez');
                                return false;
                            }
                            cb(blobInfo.blobUri(), {
                                title: file.name
                            });
                        };
                        reader.readAsDataURL(file);
                    };

                    input.click();
                },
                setup: function(editor) {
                    editor.on('init', function() {
                        const textarea = editor.targetElm;
                        if (textarea.classList.contains('no-toolbar')) {
                            editor.getContainer().querySelector('.tox-toolbar')?.remove();
                            editor.getContainer().querySelector('.tox-menubar')?.remove();
                            editor.getContainer().querySelector('.tox-statusbar')?.remove();
                        }
                    });
                    editor.on("keyup", function() {
                        console.log("editor keypressed")
                        editor.save();
                    });
                    editor.on('change', function() {
                        console.log("editor content changed")
                        editor.save();
                    })
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initTiny('textarea');
        });
    </script>
    @stack('scripts')
</body>

</html>
