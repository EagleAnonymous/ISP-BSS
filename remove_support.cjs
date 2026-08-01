const fs = require('fs');
const path = 'C:/xampp/htdocs/BSS/resources/views/subscriber/dashboard.blade.php';
let c = fs.readFileSync(path, 'utf8');
const before = c;
// Find the Support nav item and remove it
// Match from the <a> tag opening before Support to the </a> closing it
const supportStart = c.indexOf('<a href="{{ route(\'subscriber.dashboard\') }}"\n                       class="flex items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm font-medium text-white hover:bg-gray-800 hover:text-blue-300 transition">\n                         <span class="flex items-center gap-3">\n                             <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">\n                                 <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />\n                             </svg>\n                             Support\n                         </span>\n                     </a>');
if (supportStart >= 0) {
  // Find the end of the Support block (the closing </a>)
  const supportEnd = supportStart + c.substring(supportStart).indexOf('</a>') + 4;
  // Also remove trailing blank lines
  let trimEnd = supportEnd;
  while (trimEnd < c.length && (c[trimEnd] === '\n' || c[trimEnd] === '\r')) {
    trimEnd++;
  }
  c = c.substring(0, supportStart) + c.substring(trimEnd);
  fs.writeFileSync(path, c);
  console.log('Removed Support nav item');
} else {
  console.log('Support nav item not found with exact match');
}
