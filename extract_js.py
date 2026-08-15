import re

with open('resources/views/purchasing/index.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Extract the big script block (the one containing window.purchasingApp = )
match = re.search(r'<script>(.*?window\.purchasingApp\s*=.*?)</script>', content, re.DOTALL)
if match:
    js_content = match.group(1).strip()
    
    # Replace the isCeo PHP injection
    js_content = re.sub(r'const\s+isCeo\s*=\s*\{\{.*?\}\};', 'const isCeo = window.purchasingConfig ? window.purchasingConfig.isCeo : false;', js_content)
    
    # Save to public/js/purchasing.js
    with open('public/js/purchasing.js', 'w', encoding='utf-8') as js_file:
        js_file.write(js_content)
    
    # Replace the script block in Blade with the new loader
    new_script = '''<script>
    window.purchasingConfig = {
        isCeo: {{ (auth()->check() && auth()->user()->isCEO()) ? 'true' : 'false' }}
    };
</script>
<script src="{{ asset('js/purchasing.js') }}?v={{ time() }}"></script>'''
    
    new_content = content[:match.start()] + new_script + content[match.end():]
    
    with open('resources/views/purchasing/index.blade.php', 'w', encoding='utf-8') as f:
        f.write(new_content)
        
    print("Successfully extracted JS!")
else:
    print("Could not find script block")
