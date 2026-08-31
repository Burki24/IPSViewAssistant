#!/usr/bin/env python3

from __future__ import annotations

import json
import subprocess
import sys
import tempfile
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SCRIPT = ROOT / '.github' / 'scripts' / 'update_library_metadata.py'
SOURCE_SHA = '1234567890abcdef1234567890abcdef12345678'
README_PREFIX = '# IPSView Assistant\n\n'
README_SUFFIX = '\n\nUnchanged documentation.\n'


def run_update(
    file_path: Path,
    readme_path: Path,
    *,
    base_version: str,
    increment: int,
) -> tuple[dict[str, object], str]:
    subprocess.run(
        [
            sys.executable,
            str(SCRIPT),
            '--file',
            str(file_path),
            '--readme',
            str(readme_path),
            '--sha',
            SOURCE_SHA,
            '--date',
            '1785081600',
            '--increment',
            str(increment),
            '--base-version',
            base_version,
        ],
        check=True,
    )
    return (
        json.loads(file_path.read_text(encoding='utf-8')),
        readme_path.read_text(encoding='utf-8'),
    )


def expected_readme(version: str) -> str:
    return (
        README_PREFIX
        + '[![Modul Version](https://img.shields.io/badge/Modul%20Version-'
        + version
        + '-blue.svg)](library.json)'
        + README_SUFFIX
    )


def main() -> None:
    with tempfile.TemporaryDirectory() as temporary_directory:
        library_file = Path(temporary_directory) / 'library.json'
        readme_file = Path(temporary_directory) / 'README.md'
        library_file.write_text(
            json.dumps(
                {
                    'id': '{00000000-0000-0000-0000-000000000000}',
                    'version': '1.0',
                    'build': 0,
                    'date': 0,
                }
            ),
            encoding='utf-8',
        )
        readme_file.write_text(expected_readme('1.0'), encoding='utf-8')

        updated, readme = run_update(
            library_file,
            readme_file,
            base_version='1.0',
            increment=1,
        )
        if updated['version'] != '1.1':
            raise SystemExit('Expected base version 1.0 to advance to 1.1.')

        if readme != expected_readme('1.1'):
            raise SystemExit('README module version badge was not synchronized to 1.1.')

        if updated['build'] != int(SOURCE_SHA[:7], 16):
            raise SystemExit('Build number was not derived from the first seven SHA characters.')

        if updated['date'] != 1785081600:
            raise SystemExit('Unix timestamp was not written correctly.')

        updated, readme = run_update(
            library_file,
            readme_file,
            base_version='1.1',
            increment=2,
        )
        if updated['version'] != '1.3':
            raise SystemExit('Expected two source commits to advance 1.1 to 1.3.')

        if readme != expected_readme('1.3'):
            raise SystemExit('README module version badge was not synchronized to 1.3.')

        # A subsequent metadata-only run must never move an already newer
        # working-tree version backwards. The README must follow that retained
        # working-tree version as well.
        updated, readme = run_update(
            library_file,
            readme_file,
            base_version='1.0',
            increment=1,
        )
        if updated['version'] != '1.3':
            raise SystemExit('Expected an existing newer version to be preserved.')

        if readme != expected_readme('1.3'):
            raise SystemExit('README badge did not follow the preserved newer version.')

        missing_badge = Path(temporary_directory) / 'README-missing.md'
        missing_badge.write_text('# IPSView Assistant\n', encoding='utf-8')
        library_before_failure = library_file.read_text(encoding='utf-8')
        result = subprocess.run(
            [
                sys.executable,
                str(SCRIPT),
                '--file',
                str(library_file),
                '--readme',
                str(missing_badge),
                '--sha',
                SOURCE_SHA,
                '--date',
                '1785081600',
                '--increment',
                '1',
                '--base-version',
                '1.3',
            ],
            capture_output=True,
            text=True,
        )
        if result.returncode == 0:
            raise SystemExit('Metadata updater accepted a README without a module version badge.')

        if 'Expected exactly one IPSViewAssistant module version badge' not in result.stderr:
            raise SystemExit('Missing README badge did not produce the expected validation error.')

        if library_file.read_text(encoding='utf-8') != library_before_failure:
            raise SystemExit('README validation failure modified library.json before aborting.')

    print('Metadata updater regression tests passed')


if __name__ == '__main__':
    main()
