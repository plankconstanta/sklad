from urllib.request import urlopen
from collections import Counter
import re
url = urlopen('https://stepik.org/media/attachments/lesson/209717/1.html').read().decode('utf-8')
txt = str(url)
regex = '<code>(.*?)</code>'
l = sorted(re.findall(regex, txt))
print(l)
cnt = Counter(l).most_common()
print(cnt)