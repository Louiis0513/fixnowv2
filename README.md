# Fix Now

Plataforma web para conectar vecinos con técnicos locales verificados en Bogotá (electricistas, plomeros, carpinteros, etc.).

**Slogan:** Porque cada problema merece una solución rápida.

**URL local:** [http://localhost/FixNow/](http://localhost/FixNow/)

## Stack

- PHP (mysqli + sesiones)
- MySQL
- CSS propio (identidad carbón + naranja taller)

## Requisitos

- XAMPP (Apache + MySQL)
- PHP 8.0+

## Instalación

1. Coloca el proyecto en `htdocs/FixNow`.
2. Importa la base de datos:

```bash
/Applications/XAMPP/xamppfiles/bin/mysql -u root < /Applications/XAMPP/xamppfiles/htdocs/FixNow/bd/fixnow.sql
```

3. Abre: [http://localhost/FixNow/](http://localhost/FixNow/)

## Acceso admin

- URL directa (no aparece en el navbar): [http://localhost/FixNow/admin/login.php](http://localhost/FixNow/admin/login.php)
- Correo: `admin@demo.com`
- Contraseña: `Admin123!`

## Registro de técnicos

- Navbar: **Login** + **Crear**
- Alta pública: [http://localhost/FixNow/registro.php](http://localhost/FixNow/registro.php) (CC, correo, oficio, barrio…)
- Quedan `verificado=0` hasta que el admin **Acepte** y **Coloque** (barrio / pin mapa) en el panel
- Migración si ya tenías la BD: `bd/migracion_tecnico_registro.sql`

## Caché

Archivos en `cache/` (TTL ~1–4 h). Parche/invalidación al verificar, editar o eliminar técnicos y al cambiar catálogos.

## Estructura

```
FixNow/
├── admin/              # Panel (login, CRUD, bandeja)
├── bd/fixnow.sql      # Esquema y datos semilla Bogotá
├── css/estilos.css
├── img/
│   ├── logo.png
│   └── tecnicos/       # Fotos realistas de oficios
├── includes/
├── index.php
├── tecnicos.php
├── tecnico.php
├── contacto.php
└── README.md
```

## Funcionalidades

### Público

- Inicio con búsqueda, chips de oficio y guía rápida
- Login / crear cuenta de técnico (cédula + datos)
- Listado y mapa de técnicos **solo verificados**
- Cards con foto, oficio, zona y acceso WhatsApp
- Perfil del técnico + WhatsApp (registra contacto y abre `wa.me`)
- Formulario de contacto (`medio=formulario`)

### Admin

- Dashboard con conteos y últimos contactos
- CRUD especialidades y barrios
- Técnicos: pendientes, aceptar, colocar (mapa), ocultar, eliminar
- Bandeja de contactos con filtro por medio

## Seed incluido

- 9 especialidades (plomería, electricidad, gas, aires, etc.)
- 20 barrios / zonas de Bogotá y alrededores
- 18 técnicos (16 verificados) con fotos en `img/tecnicos/`
- Contactos de ejemplo (formulario y WhatsApp)
