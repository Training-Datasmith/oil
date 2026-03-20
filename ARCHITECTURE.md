# Architecture: oil (FuelPHP Oil CLI)

## Purpose

FuelPHP's command-line tool. Provides scaffolding, code generation, migration management, package installation, and an interactive REPL console for FuelPHP applications.

## Directory Structure

```
classes/
  command.php          — Entry point: parses CLI arguments and dispatches to subcommands
  console.php          — Interactive PHP REPL (Oil console / tinker)
  generate.php         — Code generator: models, controllers, migrations, tasks
  generate/
    admin.php          — Generates admin-panel scaffolding (CRUD views + controller)
    scaffold.php       — Generates full CRUD scaffold (model + controller + views + migration)
    migration/
      actions.php      — Generates migration action strings (create_table, add_column, etc.)
  refine.php           — Task runner: executes Oil Tasks (similar to Artisan commands)
  package.php          — Package manager: installs/removes FuelPHP packages via git
  exception.php        — Oil-specific exception class
```

## Key Design Decisions

- **Subcommand dispatch**: `Command` parses `$argv` and routes to `Generate`, `Refine`, `Package`, or `Console` — each is stateless and called directly
- **Template-based generation**: Generated files are built from PHP string templates embedded in `Generate` and `Generate_Admin`, not from separate stub files
- **Migration naming**: Migration filenames are prefixed with a timestamp to ensure deterministic ordering across environments

## Extension Points

- Create custom Oil Tasks in `fuel/app/tasks/` and run them via `oil refine taskname`
- Oil Tasks are plain PHP classes with a `run()` method and optional subcommand methods

## Dependency Flow

```
oil CLI script
  → Command::init()
  → Generate | Refine | Console | Package
  → FuelPHP core (DB, Config, Finder)
```
