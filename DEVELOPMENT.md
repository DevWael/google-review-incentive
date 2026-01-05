# Development Setup

## Auto-Save and Code Formatting

This project is configured with auto-save and WordPress coding standards support.

### VS Code Extensions

The following extensions are recommended (you'll be prompted to install them when you open the project):

1. **PHP Intelephense** (`bmewburn.vscode-intelephense-client`) - PHP language support
2. **phpcs** (`ikappas.phpcs`) - PHP CodeSniffer integration
3. **vscode-phpsab** (`valeryanm.vscode-phpsab`) - PHP Sniffer & Beautifier
4. **WordPress Toolbox** (`wordpresstoolbox.wordpress-toolbox`) - WordPress development tools
5. **WordPress Snippet** (`tungvn.wordpress-snippet`) - WordPress code snippets
6. **WooCommerce** (`claudiosanches.woocommerce`) - WooCommerce snippets
7. **PHP Debug** (`xdebug.php-debug`) - XDebug support

### Auto-Save Configuration

The project is configured with:
- **Auto-save**: Enabled with 1-second delay after typing
- **Format on save**: Enabled
- **Format on paste**: Enabled
- **Trim trailing whitespace**: Enabled
- **Insert final newline**: Enabled

### WordPress Coding Standards

#### Installation

The WordPress coding standards are already installed via Composer. If you need to reinstall:

```bash
composer install
```

#### Usage

**Check code for issues:**
```bash
composer phpcs
# or
./vendor/bin/phpcs
```

**Auto-fix code issues:**
```bash
composer phpcbf
# or
./vendor/bin/phpcbf
```

**Format specific file:**
```bash
./vendor/bin/phpcbf path/to/file.php
```

#### Configuration

The coding standards are configured in `.phpcs.xml.dist` with:
- WordPress Core, Docs, and Extra standards
- Text domain: `google-review-incentive`
- Prefixes: `gri`, `GRI`, `google_review_incentive`
- PHP 7.4+ compatibility checks
- Automatic fixes on save (when using VS Code)

### Manual Formatting

If you need to manually format code:

1. **In VS Code**:
   - Right-click → "Format Document"
   - Or use: `Shift + Option + F` (Mac) / `Shift + Alt + F` (Windows/Linux)

2. **Via Command Line**:
   ```bash
   composer format
   ```

### Troubleshooting

**PHPCS not working in VS Code:**
1. Ensure Composer dependencies are installed: `composer install`
2. Reload VS Code: `Cmd + Shift + P` → "Reload Window"
3. Check the Output panel: "View" → "Output" → Select "phpcs" from dropdown

**Auto-save not working:**
1. Check VS Code settings: `Cmd + ,` → Search for "auto save"
2. Ensure workspace settings aren't overriding project settings

**Format on save not working:**
1. Ensure PHP Intelephense extension is installed
2. Check that `.vscode/settings.json` is present
3. Reload VS Code window

## Code Quality

### Before Committing

Always run PHPCS before committing:
```bash
composer phpcs
```

Fix any issues automatically:
```bash
composer phpcbf
```

### CI/CD Integration

You can add these commands to your CI/CD pipeline to enforce coding standards:

```yaml
# Example for GitHub Actions
- name: Check coding standards
  run: composer phpcs
```
