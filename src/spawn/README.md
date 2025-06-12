# Spawn Point Management System

This document describes the comprehensive spawn point management system in PocketEngine, which provides advanced spawn point functionality with consistent naming throughout the project.

## Overview

The spawn point system consists of:
- **SpawnPoint**: Enhanced spawn point class with metadata, world support, and priority
- **SpawnPointManager**: Comprehensive manager for spawn point operations
- **SpawnPointCommand**: Full command system for spawn point management

## Key Features

### Enhanced SpawnPoint Class
- **Position & World**: Precise positioning with world support
- **Metadata**: Custom key-value data storage
- **Priority System**: Prioritized spawn point selection
- **Type Classification**: Categorize spawn points by type
- **Availability Control**: Enable/disable spawn points dynamically
- **Serialization**: Import/export support

### Comprehensive Management
- **CRUD Operations**: Create, read, update, delete spawn points
- **Filtering**: Filter by world, type, availability, priority
- **Validation**: Robust data validation and error handling
- **Persistence**: Automatic saving and loading
- **Import/Export**: Backup and restore functionality

## Usage Examples

### Basic Commands

```bash
# Add a spawn point at your location
/spawnpoint add "Main Spawn" world default 10

# List all spawn points
/spawnpoint list

# Get detailed information
/spawnpoint info spawn_12345

# Update spawn point properties
/spawnpoint update spawn_12345 name "Updated Name"
/spawnpoint update spawn_12345 priority 20

# Teleport to a spawn point
/spawnpoint teleport spawn_12345

# Remove a spawn point
/spawnpoint remove spawn_12345
```

### Advanced Operations

```bash
# Filter spawn points by world
/spawnpoint list world_name

# Filter by type
/spawnpoint list "" pvp

# Export spawn points
/spawnpoint export my_spawn_points.json

# Import spawn points
/spawnpoint import my_spawn_points.json true

# Clear all spawn points (with confirmation)
/spawnpoint clear confirm
```

### API Usage

```php
use JonasWindmann\PocketEngine\Main;

$spawnPointManager = Main::getInstance()->getSpawnPointManager();

// Add a spawn point
$id = $spawnPointManager->addSpawnPoint(
    new Vector3(100, 64, 200),
    "VIP Spawn",
    "lobby",
    ["vip" => true, "region" => "north"],
    10,
    "vip"
);

// Get spawn points by criteria
$vipSpawnPoints = $spawnPointManager->getSpawnPointsByType("vip");
$lobbySpawnPoints = $spawnPointManager->getSpawnPointsByWorld("lobby");
$availableSpawnPoints = $spawnPointManager->getAvailableSpawnPoints();

// Get the best spawn point
$bestSpawnPoint = $spawnPointManager->getBestSpawnPoint("lobby", "default");

// Update spawn point
$spawnPointManager->updateSpawnPoint($id, 
    name: "Premium VIP Spawn",
    priority: 15,
    available: true
);
```

## Configuration Format

Spawn points are stored in `spawnpoints.yml` with the following format:

```yaml
spawn_12345:
  name: "Main Spawn"
  x: 100.0
  y: 64.0
  z: 200.0
  world: "lobby"
  available: true
  metadata:
    region: "center"
    protected: true
  priority: 10
  type: "default"

spawn_67890:
  name: "PvP Arena Spawn"
  x: -50.0
  y: 70.0
  z: -100.0
  world: "pvp"
  available: true
  metadata:
    arena: "main"
    team: "red"
  priority: 5
  type: "pvp"
```

## Permissions

- `pocketengine.spawnpoint.use` - Basic spawn point command access
- `pocketengine.spawnpoint.add` - Add new spawn points
- `pocketengine.spawnpoint.list` - List spawn points
- `pocketengine.spawnpoint.remove` - Remove spawn points
- `pocketengine.spawnpoint.info` - View spawn point details
- `pocketengine.spawnpoint.update` - Update spawn point properties
- `pocketengine.spawnpoint.teleport` - Teleport to spawn points
- `pocketengine.spawnpoint.clear` - Clear all spawn points
- `pocketengine.spawnpoint.export` - Export spawn points
- `pocketengine.spawnpoint.import` - Import spawn points

## Migration from Legacy System

The system maintains backward compatibility with the old format:
- Legacy `"x:y:z"` format is automatically converted
- Old `setspawnpoint` command still works but shows deprecation warning
- Existing spawn points are preserved during updates

## Best Practices

1. **Consistent Naming**: Always use "spawn point" (two words) in documentation and messages
2. **World Assignment**: Always specify world names for spawn points
3. **Priority System**: Use priorities to control spawn point selection order
4. **Type Classification**: Use types to organize spawn points by purpose
5. **Regular Backups**: Export spawn points regularly for backup purposes
6. **Metadata Usage**: Store additional information in metadata for custom functionality

## Troubleshooting

### Common Issues

1. **Spawn point not found**: Check the ID with `/spawnpoint list`
2. **World not loaded**: Ensure the world is loaded before teleporting
3. **Permission denied**: Check user permissions for spawn point commands
4. **Import failed**: Verify JSON format and file location

### Debug Commands

```bash
# List all spawn points with full details
/spawnpoint list

# Check specific spawn point information
/spawnpoint info <id>

# Verify spawn point availability
/spawnpoint list "" "" 1
```
