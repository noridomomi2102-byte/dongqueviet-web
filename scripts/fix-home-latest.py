from pathlib import Path
import re

p = Path(__file__).resolve().parent.parent / "resources/views/frontend/home.blade.php"
t = p.read_text(encoding="utf-8")

pattern = (
    r"\n    \{\{-- removed: tin moi nhat --\}\}"
    r".*?"
    r"\n    <div class=\"pagination\">\{\{ \$latest->links\(\) \}\}</div>\n"
)

new_t, n = re.subn(pattern, "\n", t, count=1, flags=re.DOTALL)
if n == 0:
    raise SystemExit("block not found")

p.write_text(new_t, encoding="utf-8")
print("ok")
