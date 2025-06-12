# PocketEngine

**PocketEngine** is a powerful and flexible minigame framework for PocketMine-MP that provides a comprehensive set of APIs and tools for creating custom minigames. Built on top of CoreAPI, it offers a robust foundation for game development with features like game state management, spawn point systems, world management, and integrated scoreboard support.

## 🚀 Features

### Core Game Framework
- **Game State Management**: Automatic handling of game states (WAITING, STARTING, PLAYING, ENDING)
- **Player Management**: Seamless player joining, leaving, and session tracking
- **Round-based Gameplay**: Configurable round timers and player requirements
- **Winner Detection**: Built-in winner detection and game ending logic
- **Post-Game Reset**: Automatic server reset with configurable countdown and player notifications

### World Management
- **Automatic World Setup**: Creates and manages required worlds (lobby, game, victory)
- **World Cleanup**: Removes unwanted worlds and maintains clean server state
- **World Configuration**: Automatic time and weather settings for optimal gameplay

### Spawn Point System
- **Dynamic Spawn Points**: Flexible spawn point creation and management
- **Priority System**: Weighted spawn point selection
- **Metadata Support**: Custom data attachment to spawn points
- **Type Classification**: Categorize spawn points by type (default, vip, special, etc.)
- **World-specific Spawns**: Assign spawn points to specific worlds

### Scoreboard Integration
- **Real-time Updates**: Live game statistics and information display
- **Multiple Scoreboards**: Different scoreboards for different game states
- **Custom Tags**: Dynamic content with lambda functions
- **Automatic Management**: Seamless integration with CoreAPI's scoreboard system

### Configuration Management
- **Type-safe Configuration**: Validated configuration with proper type checking
- **Setup Mode**: Development-friendly setup mode for testing
- **Flexible Settings**: Customizable game parameters and behavior

## 📋 Requirements

- **PocketMine-MP**: 5.28.0 or higher
- **CoreAPI**: Latest version (dependency) [https://github.com/DerCooleVonDem/CoreAPI]
- **PHP**: 8.1 or higher

## 🛠️ Installation

1. **Download PocketEngine** and place it in your `plugins/` folder
2. **Install CoreAPI** (required dependency)
3. **Start your server** to generate configuration files
4. **Configure the plugin** according to your needs

## ⚙️ Configuration

### Basic Configuration (`config.yml`)

```yaml
# PocketEngine configuration file v1
enable-auto-world-management: false  # Enable automatic world management
setup-mode: true                     # Enable setup mode for development
minigame-name: "Minigame"           # Display name for your minigame
required-players: 2                  # Minimum players to start a game
round-time: 5                       # Round duration in minutes
post-game-wait-time: 30             # Post-game countdown time in seconds
```

### Important Configuration Notes

- **`enable-auto-world-management`**: When enabled, PocketEngine will take full control of your world folders. This will delete existing worlds and create new ones. Only enable this if you're sure!
- **`setup-mode`**: Enables development features and additional logging
- **`required-players`**: Minimum number of players needed to start a game
- **`round-time`**: Maximum duration for each game round in minutes
- **`post-game-wait-time`**: Time in seconds to wait after a round ends before resetting the server (minimum: 5 seconds)

## 🎮 Commands

### Main Commands

#### `/pocketengine` (aliases: `/pe`, `/pocket`)
Main PocketEngine command with the following subcommands:

- **`/pocketengine info`** - Display plugin information and status
- **`/pocketengine switchworld <world_name>`** - Switch to a different world
- **`/pocketengine setspawnpoint`** - Set a spawn point at your current location

#### `/spawnpoint` (alias: `/sp`)
Dedicated spawn point management command:

- **`/spawnpoint add [name] [type] [priority]`** - Add a spawn point at your location
- **`/spawnpoint remove <id>`** - Remove a spawn point by ID
- **`/spawnpoint list [page]`** - List all spawn points with pagination
- **`/spawnpoint info <id>`** - Show detailed information about a spawn point
- **`/spawnpoint teleport <id>`** - Teleport to a specific spawn point
- **`/spawnpoint clear confirm`** - Clear all spawn points (requires confirmation)

## 🔧 API Usage

### Game Management

```php
use JonasWindmann\PocketEngine\Main;
use JonasWindmann\PocketEngine\game\Game;

// Get the current game instance
$game = Main::getInstance()->game;

// Check game state
if ($game->gameState === GameState::WAITING) {
    // Game is waiting for players
}

// Add a player to the game
$game->join($player);

// Start the game manually
$game->start();

// End the game
$game->end();
```

### Spawn Point Management

```php
use JonasWindmann\PocketEngine\Main;
use pocketmine\math\Vector3;

$spawnManager = Main::getInstance()->getSpawnPointManager();

// Add a spawn point
$id = $spawnManager->addSpawnPoint(
    new Vector3(100, 64, 200),
    "VIP Spawn",
    "lobby",
    ["vip" => true, "region" => "north"],
    10,
    "vip"
);

// Get a spawn point
$spawnPoint = $spawnManager->getSpawnPoint($id);

// Remove a spawn point
$spawnManager->removeSpawnPoint($id);

// Get all spawn points
$allSpawns = $spawnManager->getAllItems();
```

### World Management

```php
use JonasWindmann\PocketEngine\Main;

$worldManager = Main::getInstance()->getWorldManager();

// Check if a world is required by PocketEngine
if ($worldManager->isRequiredWorld("lobby")) {
    // This world is managed by PocketEngine
}

// Get list of required worlds
$requiredWorlds = $worldManager->getRequiredWorlds(); // ["lobby", "game", "victory"]
```

### Configuration Access

```php
use JonasWindmann\PocketEngine\Main;

$config = Main::getInstance()->getConfigurationManager();

// Check if in setup mode
if ($config->isSetupMode()) {
    // Development mode is enabled
}

// Get game settings
$requiredPlayers = $config->getRequiredPlayers();
$roundTime = $config->getRoundTime();
$minigameName = $config->getMinigameName();
```

## 🔐 Permissions

### Basic Permissions
- **`pocketengine.use`** - Access to basic PocketEngine commands (default: op)
- **`pocketengine.spawnpoint.use`** - Access to spawn point management commands (default: op)

### Command-specific Permissions
All commands respect the permission system and will only be available to users with appropriate permissions.

## 🏗️ Architecture

PocketEngine follows a clean architecture pattern with several key components:

### Managers
- **ConfigurationManager**: Handles all configuration loading and validation
- **WorldManager**: Manages world creation, deletion, and configuration
- **SpawnPointManager**: Handles spawn point storage and retrieval
- **GameServiceManager**: Manages game-specific services and updates
- **ScoreboardSetupManager**: Sets up and configures scoreboards

### Game System
- **Game**: Core game logic and state management
- **GameState**: Enumeration of possible game states
- **GameListener**: Event handling for game-related events
- **GameUpdateTask**: Handles periodic game updates

### Components
- **PocketEngineComponent**: Player session component for game-specific data

## 🔄 Game Flow

1. **Initialization**: Plugin loads and initializes all managers
2. **World Setup**: Required worlds are created/configured
3. **Waiting State**: Game waits for minimum required players
4. **Starting State**: Countdown begins when enough players join
5. **Playing State**: Game is active, players are teleported to spawn points
6. **Ending State**: Game ends either by time limit or win condition
7. **Post-Game Countdown**: Configurable wait time with player notifications
8. **Reset**: All players kicked, game returns to waiting state for next round

## 🎯 Post-Game Reset System

PocketEngine features an advanced post-game reset system that provides a smooth transition between rounds:

### Features
- **⏱️ Configurable Countdown**: Set custom wait time after rounds end (default: 30 seconds)
- **📊 Live Scoreboard Updates**: Victory scoreboard shows countdown timer in real-time
- **⚠️ Warning System**: Title notifications at 30, 20, 10, 5, 4, 3, 2, 1 seconds before reset
- **👥 Automatic Player Management**: All remaining players are kicked with friendly messages
- **🔄 Clean Reset**: Server returns to pristine waiting state, ready for new players

### How It Works
1. **Round Ends** - Players see victory/defeat titles and are moved to victory world
2. **Countdown Begins** - Victory scoreboard displays "Reset in: Xs" countdown
3. **Warnings Displayed** - Players receive title warnings at specified intervals
4. **Players Kicked** - Friendly kick message: "Round Over! The server is resetting for the next round. Rejoin to play again!"
5. **Server Reset** - Game state, player lists, and all variables reset to initial values
6. **Ready for Next Round** - New players can join immediately

### Configuration
```yaml
post-game-wait-time: 30  # Time in seconds (minimum: 5)
```

This system ensures players understand what's happening and when they can rejoin, creating a professional minigame experience.

## 🐛 Development & Debugging

### Setup Mode
Enable `setup-mode: true` in your configuration for:
- Additional debug logging
- Development-friendly features
- Enhanced error reporting

### Logging
PocketEngine provides comprehensive logging for:
- Game state changes
- Player actions
- World management operations
- Spawn point operations
- Configuration validation

## 📚 Examples

Check out the included example minigames:
- **MoonShooterMinigame**: A complete example implementation showing how to extend PocketEngine

## 🤝 Contributing

PocketEngine is part of the CoreAPI ecosystem. Contributions are welcome! Please ensure your code follows the established patterns and includes appropriate documentation.

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🔗 Links

- **CoreAPI**: The foundation framework that PocketEngine builds upon
- **Documentation**: Additional documentation can be found in the `/docs` folder
- **Examples**: See `/examples` for complete minigame implementations

---

**Note**: PocketEngine is currently in active development. Some features may change in future versions. Always backup your server before enabling auto-world-management!
