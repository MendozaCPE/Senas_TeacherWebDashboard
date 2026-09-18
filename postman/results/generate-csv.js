const fs = require('fs');
console.log('Reading JSON results...');
const data = JSON.parse(fs.readFileSync('postman/results/run-results.json', 'utf8'));

const rows = ['Iteration,Endpoint,Method,HTTP Status,Response Time (ms),Response Size (bytes),Result'];

data.run.executions.forEach((exec, i) => {
    const iter = (exec.cursor ? exec.cursor.iteration : Math.floor(i / 8)) + 1;
    const name = exec.item.name.replace(/,/g, ' ');
    const method = exec.request.method;
    const status = exec.response ? exec.response.code : 'N/A';
    const time = exec.response ? exec.response.responseTime : 'N/A';
    const size = exec.response ? exec.response.responseSize : 'N/A';
    const hasFail = exec.assertions && exec.assertions.some(a => a.error);
    const result = hasFail ? 'FAIL' : 'PASS';
    rows.push(`${iter},"${name}",${method},${status},${time},${size},${result}`);
});

fs.writeFileSync('postman/results/detailed-report.csv', rows.join('\n'));
console.log('Done! Total rows: ' + (rows.length - 1));
