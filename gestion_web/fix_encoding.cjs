const fs = require('fs');

function fix(file, regex, replacement) {
  let c = fs.readFileSync(file, 'utf8');
  c = c.replace(regex, replacement);
  fs.writeFileSync(file, c, 'utf8');
}

fix('src/collections/Pages/index.ts', /labels:\s*\{\s*singular:\s*'[^']+',\s*plural:\s*'[^']+',\s*\}/g, "labels: { singular: 'Página', plural: 'Páginas' }");
fix('src/collections/Posts/index.ts', /labels:\s*\{\s*singular:\s*'[^']+',\s*plural:\s*'[^']+'\s*\}/g, "labels: { singular: 'Publicación', plural: 'Publicaciones' }");
fix('src/collections/Categories.ts', /labels:\s*\{\s*singular:\s*'[^']+',\s*plural:\s*'[^']+'\s*\}/g, "labels: { singular: 'Categoría', plural: 'Categorías' }");
fix('src/Footer/config.ts', /label:\s*'[^']+',/g, "label: 'Pie de Página',");
