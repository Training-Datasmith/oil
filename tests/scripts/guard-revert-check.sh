#!/usr/bin/env bash
set -euo pipefail
ROOT=/workspace
PHP72=php@sha256:42ffbc0798e4449bbd1e14fc4dcb87774aa1ad1900a09ef6a965bc0880aa2161
run_test() {
  sudo docker run --rm -v "$ROOT":/app -w /app "$PHP72" bash -lc \
    'php -d error_reporting=-1 vendor/bin/phpunit --filter '"$1"' --order-by=default >/tmp/guard.out 2>&1; echo $?'
}

checks=(
  "4.1|CommandTest::testFlagBeforeSubcommandStillGenerates|classes/command.php|array_values"
  "4.2|NormalizeArgsTest::testBracketedNullOne|classes/generate.php|part_matches = \$part_matches\[0\]"
  "4.3|GenerateViewsTest::testWithTestOneFilePerAction|classes/generate.php|\$action"
  "4.4|RefineTest::testFarNameHasNoSuggestion|classes/refine.php|key(\$possibilities)"
  "4.5|RefineTest::testHelpTwiceLoadsTasksOnce|classes/refine.php|require_once"
  "4.6|FromdbTest::testListTablesFailurePrintsDriverMessage|tasks/fromdb.php|FuelException"
  "4.7|GenerateModuleTest::testModuleWithoutFoldersOption|classes/generate.php|is_string(\$folders)"
  "4.8|GenerateMigrationTest::testFirstMigrationNumberWhenDirMissing|classes/generate.php|is_dir(\$migrations_path)"
)

for item in "${checks[@]}"; do
  IFS='|' read -r id filter file needle <<<"$item"
  echo "=== $id revert check ($filter) ==="
  git -C "$ROOT" stash push -q -m "guard-$id" -- "$file" || true
  git -C "$ROOT" checkout HEAD -- "$file" 2>/dev/null || git -C "$ROOT" show HEAD:"$file" >/dev/null
  # restore pre-fix from stash parent: we need version WITHOUT fix - stash has current; checkout previous commit for file
  git -C "$ROOT" show HEAD~0:"$file" >/dev/null 2>&1 || true
done
