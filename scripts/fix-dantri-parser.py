from pathlib import Path

p = Path(__file__).resolve().parent.parent / "app/Services/ExternalNewsCrawler.php"
t = p.read_text(encoding="utf-8")

old_block = """        $contentNode = null;
        foreach ($xpath->query('//article/div') as $motion) {
            if ($motion instanceof \\DOMElement && $motion->getAttribute('class') === '') {
                $contentNode = $motion;
                break;
            }
        }"""

new_block = """        $contentNode = null;
        foreach ($xpath->query('//article/div') as $node) {
            if ($node instanceof \\DOMElement && $node->getAttribute('class') === '') {
                $contentNode = $node;
                break;
            }
        }"""

# disk may have $div + $motion mix
alt_old = """        $contentNode = null;
        foreach ($xpath->query('//article/div') as $motion) {
            if ($motion instanceof \\DOMElement && $motion->getAttribute('class') === '') {
                $contentNode = $motion;
                break;
            }
        }"""

for candidate in [
    old_block.replace("$motion", "$div").replace("$motion", "$div"),
    """        $contentNode = null;
        foreach ($xpath->query('//article/div') as $motion) {
            if ($motion instanceof \\DOMElement && $motion->getAttribute('class') === '') {
                $contentNode = $motion;
                break;
            }
        }""",
    """        $contentNode = null;
        foreach ($xpath->query('//article/div') as $motion) {
            if ($motion instanceof \\DOMElement && $motion->getAttribute('class') === '') {
                $contentNode = $motion;
                break;
            }
        }""",
]:
    pass

# simpler: line by line fixes
import re

t = re.sub(
    r"foreach \(\$xpath->query\('//article/div'\) as \$\w+\)",
    "foreach ($xpath->query('//article/div') as $node)",
    t,
)
t = re.sub(
    r"if \(\$motion instanceof \\\\DOMElement && \$\w+->getAttribute\('class'\) === ''\)",
    "if ($node instanceof \\DOMElement && $node->getAttribute('class') === '')",
    t,
)
t = re.sub(
    r"\$contentNode = \$\w+;",
    "$contentNode = $node;",
    t,
    count=1,
)

t = t.replace(
    "'<motion class=\"article-sapo\"><p>'.e($excerpt).'</p></div>'",
    "'<div class=\"article-sapo\"><p>'.e($excerpt).'</p></div>'",
)
t = t.replace(
    "'<motion class=\"article-sapo\"><p>'.e($excerpt).'</p></motion>'",
    "'<div class=\"article-sapo\"><p>'.e($excerpt).'</p></div>'",
)
t = t.replace(
    "'content' => str_replace(['<motion', '</motion>'], ['<div', '</div>'], $content),",
    "'content' => $content,",
)

p.write_text(t, encoding="utf-8")
print("saved")
