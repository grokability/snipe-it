const express = require('express');
const app = express();
app.use(express.json()); // Permitir que la API entienda JSON

// Nuestro carrito empieza vacío
let carrito = [];

// Endpoint 1: Añadir artículo al carrito
app.post('/carrito', (req, res) => {
    const { articulo } = req.body;
    carrito.push(articulo);
    res.status(201).json({ mensaje: "Artículo añadido", carrito });
});

// Endpoint 2: Ver el contenido del carrito
app.get('/carrito', (req, res) => {
    res.status(200).json({ totalArticulos: carrito.length, items: carrito });
});

// Exportamos la app para que Supertest la pueda usar sin encender puertos
module.exports = app;
