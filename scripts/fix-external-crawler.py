from pathlib import Path
import re

p = Path(__file__).resolve().parent.parent / "app/Services/ExternalNewsCrawler.php"
t = p.read_text(encoding="utf-8")

t = t.replace(
    """        foreach ($xpath->query('//article/div') as $motion) {
            if ($motion instanceof \\DOMElement && $motion->getAttribute('class') === '') {
                $contentNode = $motion;""",
    """        foreach ($xpath->query('//article/div') as $node) {
            if ($node instanceof \\DOMElement && $node->getAttribute('class') === '') {
                $contentNode = $node;""",
)

# fix if still old version with $motion
t = t.replace(
    """        foreach ($xpath->query('//article/div') as $motion) {
            if ($motion instanceof \\DOMElement && $motion->getAttribute('class') === '') {
                $contentNode = $motion;""",
    """        foreach ($xpath->query('//article/div') as $node) {
            if ($node instanceof \\DOMElement && $node->getAttribute('class') === '') {
                $contentNode = $node;""",
)

t = t.replace('<motion class="article-sapo">', '<div class="article-sapo">')
t = t.replace("['<motion', '</motion>'], ['<div', '</motion>']", "['<div', '</div>'], ['<motion', '</motion>']")
t = t.replace(
    "'content' => str_replace(['<div', '</div>'], ['<motion', '</motion>'], $content),",
    "'content' => $content,",
)

p.write_text(t, encoding="utf-8")
print("fixed", "motion" in t)
