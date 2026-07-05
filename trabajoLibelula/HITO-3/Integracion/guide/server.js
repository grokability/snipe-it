const express = require('express');
const app = express();
app.use(express.json());

// Base de datos simulada en memoria
let productos = [];
let idContador = 1;

// [POST] Crear producto
app.post('/api/productos', (req, res) => {
    const { nombre, precio } = req.body;
    if (!nombre || !precio) {
        return res.status(400).json({ error: "Faltan campos requeridos" });
    }
    const nuevoProducto = { id: idContador++, nombre, precio: Number(precio) };
    productos.push(nuevoProducto);
    res.status(201).json(nuevoProducto);
});

// [GET] Obtener un producto por ID
app.get('/api/productos/:id', (req, res) => {
    const producto = productos.find(p => p.id === parseInt(req.params.id));
    if (!producto) return res.status(404).json({ error: "Producto no encontrado" });
    res.status(200).json(producto);
});

// [PUT] Actualizar producto por ID
app.put('/api/productos/:id', (req, res) => {
    const producto = productos.find(p => p.id === parseInt(req.params.id));
    if (!producto) return res.status(404).json({ error: "Producto no encontrado" });

    const { nombre, precio } = req.body;
    if (nombre) producto.nombre = nombre;
    if (precio) producto.precio = Number(precio);

    res.status(200).json(producto);
});

// [DELETE] Eliminar producto
app.delete('/api/productos/:id', (req, res) => {
    const index = productos.findIndex(p => p.id === parseInt(req.params.id));
    if (index === -1) return res.status(404).json({ error: "Producto no encontrado" });

    productos.splice(index, 1);
    res.status(200).json({ mensaje: "Producto eliminado correctamente" });
});

const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Servidor corriendo en http://localhost:${PORT}`);
});