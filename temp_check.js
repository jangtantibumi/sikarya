
        (function() {
            var theme = localStorage.getItem('sikarya_theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
            var color = localStorage.getItem('sikarya_color');
            if(color) {
                document.documentElement.style.setProperty('--accent', color);
                document.documentElement.style.setProperty('--accent-hover', color);
                document.documentElement.style.setProperty('--accent-active', color);
            }
        })();
    