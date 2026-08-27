const fs = require('fs');
let code = fs.readFileSync('resources/views/pages/products/create.blade.php', 'utf8');
code = code.substring(code.indexOf('<script>') + 8, code.lastIndexOf('</script>'));
code = code.replace(/@php/g, '/*').replace(/@endphp/g, '*/');
code = code.replace(/@foreach.*?$/gm, '');
code = code.replace(/@endforeach.*?$/gm, '');
code = code.replace(/@json\(.*?\)/g, '[]');
code = code.replace(/{{.*?}}/g, '""');
fs.writeFileSync('temp.js', code);
