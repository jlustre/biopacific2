import json
import re
from pathlib import Path

import openpyxl

from generate_performance_items_seeder import (
    FILES,
    POSITION_TITLE_TO_TEMPLATE,
    extract_template_items,
)

SEEDER = Path(__file__).resolve().parents[1] / "database/seeders/EmployeePerformanceItemsSeeder.php"


def parse_seeder_items() -> list[tuple[str, str, list[str]]]:
    text = SEEDER.read_text(encoding="utf-8")
    pattern = re.compile(
        r"'section' => '((?:\\'|[^'])*)',\s*'item' => '((?:\\'|[^'])*)',\s*'categories' => \[([^\]]*)\]",
        re.S,
    )
    rows = []
    for section, item, cats in pattern.findall(text):
        rows.append((section.replace("\\'", "'"), item.replace("\\'", "'"), re.findall(r"'([^']+)'", cats)))
    return rows


def main() -> None:
    seeder_rows = parse_seeder_items()
    seeder_by_cat = {cat: set() for cat in FILES}
    for section, item, categories in seeder_rows:
        for category in categories:
            seeder_by_cat[category].add((section, item))

    print("Template validation (Excel scorable items vs seeder category tags):\n")
    ok = True
    for category in FILES:
        excel_items = set(extract_template_items(category))
        seeded_items = seeder_by_cat.get(category, set())
        missing = excel_items - seeded_items
        extra = seeded_items - excel_items
        status = "OK" if not missing and not extra else "MISMATCH"
        if status != "OK":
            ok = False
        print(f"{category}: excel={len(excel_items)} seeded={len(seeded_items)} missing={len(missing)} extra={len(extra)} [{status}]")
        for section, item in sorted(missing)[:5]:
            print(f"  MISSING [{section}] {item[:110]}")
        for section, item in sorted(extra)[:3]:
            print(f"  EXTRA   [{section}] {item[:110]}")
        print()

    print(f"Position titles mapped: {len(POSITION_TITLE_TO_TEMPLATE)}")
    print("Overall:", "PASS" if ok else "FAIL")


if __name__ == "__main__":
    main()
