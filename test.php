<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estilo para Input de Números</title>
    <style>
        /* Estilo aplicado al input */
        .selectors-container input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: white;
            box-sizing: border-box; /* Asegura que el padding no aumente el tamaño del input */
        }

        /* Estilo para inputs válidos */
        .selectors-container input:valid {
            border: 1px solid #4CAF50;
            background-color: #e8f5e9;
        }

        /* Estilo para inputs inválidos */
        .selectors-container input:invalid {
            border: 1px solid #f44336;
            background-color: #ffebee;
        }
    </style>
</head>
<body>
    <div class="selectors-container">
        <label for="integerInput">Número entero:</label>
        <input 
            type="text" 
            id="integerInput" 
            name="integerInput" 
            required 
            placeholder="Ingrese solo números"
            oninput="validateNumber(this);"
            pattern="\d+"
            title="Por favor, ingrese solo números enteros.">
    </div>

    <script>
        function validateNumber(input) {
            // Permite solo números (elimina todo lo que no sea un dígito)
            input.value = input.value.replace(/[^0-9]/g, '');
        }
    </script>
</body>
</html>
