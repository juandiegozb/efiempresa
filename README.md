# Efiempresa API Documentation

## Get all Products
- **Endpoint:** `/api/products/list`
- **Method:** GET
- **Authorization:** OAuth 2.0 - *opcional*

- *Método para obtener todos los productos, es opcional usar el auth, pero para agregar al carrito de compras y que puedas ver opciones adicionales como url si es correcto autenticarse*

- **Response:**
  ```json
  {
      "status": "success",
      "total_items": 2,
      "data": {
          "products": [
              {
                  "id": 4,
                  "user_id": 3,
                  "category_id": 1,
                  "name": "Producto uno",
                  "status": true,
                  "price": "50.99",
                  "stock": 3,
                  "ean_13": "1234567891234",
                  "created_at": "2023-12-28T02:20:05.000000Z",
                  "updated_at": "2023-12-28T02:20:05.000000Z",
                  "canPurchase": true
              }
          ],
          "user_products": {
              "1": {
                  "id": 5,
                  "user_id": 4,
                  "category_id": 2,
                  "name": "Macbook Pro 14 pulgadas",
                  "status": true,
                  "price": "1399.99",
                  "stock": 10,
                  "ean_13": "1234567891225",
                  "created_at": "2023-12-28T02:29:31.000000Z",
                  "updated_at": "2023-12-28T02:29:31.000000Z",
                  "actions": {
                      "edit": "http://localhost:8000/api/products/update/5",
                      "delete": "http://localhost:8000/api/products/destroy/5"
                  }
              }
          }
      }
  }
  
## Register a User
- **Endpoint:** `/api/register`
- **Method:** POST
- **Request:**
```json
    {
        "name": "usuario 1",
        "email": "email_uno@correo.com",
        "password": "qwertyas",
        "password_confirmation": "qwertyas"
    }
```

- **Authorization:** None
- **Descripción:** Metodo usado para registrar un usuario a la aplicación, como respuesta recibes un *JSON* con un **token** que usaras para las rutas privadas que lo necesiten.


## Store a Product
- **Endpoint:** `/api/products`
- **Method:** POST
- **Authorization:** OAuth 2.0
- **Request:**
```json
{
"category_id": 1,
"name": "Producto uno",
"price": 50.99,
"stock": 3,
"ean_13": 1234567891234
}
```
- **Descripción:** *Método que usa para registrar un producto, esta ruta es privada y requiere que esté autenticado con el token compartido al momento de loguearse y/o registrarse al sistema*

## Login User
- **Endpoint:** `api/login` 
- **Method:** POST
Request:
```json
{
"email": "correo@dominio.com",
"password": "asdf"
}
```
- **Descripción:** *Se usa para loguearse al sistema **NO** es necesario el token, ya que al acceder esté metodo lo comparte*

## Logout User

- **Endpoint:** `api/logout`
- **Method:** GET
- **Authorization:** Bearer Token
- **Token:** OAuth 2.0
- **Descripción:** *Elimina la sesión y el token del usuario autenticado, no es necesario ningun parametro SOLAMENTE el token

## Delete a Product
- **Endpoint:** `api/products/destroy/{id Producto}`
- **Method:** DELETE
- **Authorization:** OAuth 2.0
- Elimina el producto con el **id** compartido, **NO** elimina productos que no sean del usuario autenticado, es **OBLIGATORIO** que estes autenticado para continuar con el proceso.

## Add Product to Cart
- **Endpoint:** `api/products/add-to-cart/{id producto}`
- **Method:** POST
- OAuth 2.0
- **Authorization:** 
- _Agrega productos al carrito de compras NO puede agregar sus propios productos, solo se agregan si cumplen con las condiciones:_
 1. el producto tiene un stock mayor a 0
2. el producto tiene un status activo
3. el producto es comprable comprable `canPurchase`

## Update a Product
- **Endpoint:** `/api/products/update/{id producto}`
- **Method:** PUT
- **Authorization:** OAuth 2.0

Request:

```json
{
"category_id": 1,
"name": "Nuevo producto editado",
"price": 30.50,
"stock": 50,
"ean_13": "1234515892586"
}
```
- _**Descripción:** Se crea el método que permite actualizar el producto, solo se puede modificar propios productos._
