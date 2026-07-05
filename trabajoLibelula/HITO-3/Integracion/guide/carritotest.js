const request = require('supertest');
const app = require('./app'); // Importamos nuestra API

describe('Prueba de Integración del Carrito de Compras', () => {

    it('Debería añadir un producto y luego mostrarlo en el carrito', async () => {
        
        // PASO 1: Agregamos un artículo mediante un POST
        const respuestaPost = await request(app)
            .post('/carrito')
            .send({ articulo: 'Monitor Gamer 24"' });

        // Validamos que el servidor responda que se creó con éxito (201)
        expect(respuestaPost.statusCode).toBe(201);
        expect(respuestaPost.body.mensaje).toBe("Artículo añadido");

        // -------------------------------------------------------------
        // PASO 2 (INTEGRACIÓN): Consultamos el carrito con un GET 
        // para verificar que el estado realmente cambió y el producto sigue ahí.
        const respuestaGet = await request(app)
            .get('/carrito');

        // Validamos que la base de datos temporal guardó el artículo del paso anterior
        expect(respuestaGet.statusCode).toBe(200);
        expect(respuestaGet.body.totalArticulos).toBe(1);
        expect(respuestaGet.body.items).toContain('Monitor Gamer 24"');
    });

});
