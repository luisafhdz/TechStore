const express = require('express');

const app = express();

app.get('/products', (req, res) => {
    res.json([
        {
            id: 1,
            name: 'Laptop'
        },
        {
            id: 2,
            name: 'Mouse'
        }
    ]);
});

app.listen(3000, () => {
    console.log('Microservicio ejecutándose en puerto 3000');
});