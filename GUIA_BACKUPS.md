# Guía: Módulo de Respaldos (Backups)

## 📋 Descripción General

Este módulo permite crear respaldos de la base de datos de la farmacia de dos formas:
- **Manual**: desde la interfaz web o desde la terminal
- **Automático**: se ejecuta automáticamente cada día a las 2:00 AM

---

## 🔧 Cómo Usar: Forma Manual

### Desde la Interfaz Web
1. Inicia la aplicación: `php artisan serve`
2. Entra a **Backups** desde el menú lateral
3. Haz clic en el botón verde **"Crear respaldo ahora"**
4. Espera a que se cree el archivo (normalmente tarda pocos segundos)
5. Verás una lista con todos los respaldos disponibles

### Desde la Terminal (Línea de Comandos)
Ejecuta este comando en la carpeta del proyecto:

```bash
php artisan backup:database
```

**Salida esperada:**
```
Ejecutando respaldo...
✓ Respaldo creado exitosamente: backup_2026_07_28_12_32_13.sql (0.07 MB)
```

---

## 📅 Cómo Funciona: Respaldo Automático

El sistema está configurado para hacer un respaldo automático **cada día a las 2:00 AM**.

### Para que funcione el respaldo automático, necesitas:

1. **Ejecutar el scheduler de Laravel** en segundo plano (comando en terminal):
   ```bash
   php artisan schedule:work
   ```
   
   O agregarlo a una tarea programada del sistema (Windows Task Scheduler, cron en Linux, etc.)

2. **En Windows con Task Scheduler:**
   - Abre Task Scheduler
   - Crea una nueva tarea programada
   - Configura: `php "C:\ruta\del\proyecto\artisan" schedule:work`
   - Hazla ejecutar continuamente

3. **En Linux/macOS con crontab:**
   - Añade esta línea al crontab:
     ```bash
     * * * * * cd /ruta/del/proyecto && php artisan schedule:run >> /dev/null 2>&1
     ```

---

## 💾 Acciones Disponibles

### En la Interfaz Web

| Acción | Botón | Descripción |
|--------|-------|-------------|
| **Descargar** | 📥 | Descarga el archivo SQL a tu computadora |
| **Restaurar** | 🔄 | Recarga la base de datos con ese respaldo |
| **Crear nuevo** | ✅ | Genera un nuevo respaldo |

---

## 📁 Dónde se Guardan los Respaldos

Todos los archivos se guardan en:
```
storage/app/backups/
```

Los archivos tienen este formato de nombre:
```
backup_YYYY_MM_DD_HH_MM_SS.sql
```

Ejemplo:
```
backup_2026_07_28_12_32_13.sql
```

---

## ⚠️ Consideraciones Importantes

1. **Antes de restaurar**: El proceso sobrescribe la base de datos actual. Asegúrate de tener un respaldo antes.

2. **Espacio en disco**: Los respaldos ocupan espacio. Revisa regularmente y elimina los antiguos si es necesario.

3. **Automatización**: Si solo ejecutas `php artisan serve`, los respaldos automáticos NO se ejecutarán. Necesitas una tarea programada o ejecutar `php artisan schedule:work`.

4. **Permisos**: La carpeta `storage/app/backups/` necesita permisos de escritura.

---

## 🐛 Solución de Problemas

### El respaldo no se crea en la interfaz web
```
❌ "No se pudo crear el respaldo"
```

**Solución:**
- Verifica que MySQL esté ejecutándose en XAMPP
- Revisa que los archivos existan en: `C:\xampp\mysql\bin\mysqldump.exe`
- Revisa el log: `storage/logs/laravel.log`

### El respaldo automático no se ejecuta
**Solución:**
- Debes ejecutar `php artisan schedule:work` en una terminal aparte
- O configurar una tarea programada del sistema operativo

### No puedo restaurar un respaldo
```
❌ "No se pudo restaurar la base de datos"
```

**Solución:**
- Verifica que el archivo SQL exista y no esté corrupto
- Verifica que MySQL esté ejecutándose
- Intenta desde la terminal: `php artisan backup:database`

---

## 📊 Estadísticas en la Pantalla

- **Total de respaldos**: Número de archivos guardados
- **Tamaño total**: Espacio ocupado por todos los respaldos
- **Ubicación**: Carpeta donde están guardados

---

## 🔄 Ejemplo Completo

### Paso 1: Crear un respaldo manual
```bash
php artisan backup:database
```
Resultado: `backup_2026_07_28_12_32_13.sql (0.07 MB)`

### Paso 2: Verificar en la web
- Ve a **Backups** en el menú
- Verás el archivo recién creado

### Paso 3: Descargarlo
- Haz clic en 📥 Descargar

### Paso 4: Si necesitas restaurar
- Haz clic en 🔄 Restaurar
- Confirma la acción

---

## ✅ Verificación Final

Para verificar que todo está bien configurado:
```bash
php artisan list backup:database
```

Deberías ver el comando listado sin errores.

---

**¿Dudas?** Revisa el log en: `storage/logs/laravel.log`
