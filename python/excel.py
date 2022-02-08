import xlrd3, wget
from statistics import median, mean








exit


wb = xlrd3.open_workbook('tab.xlsx')
sheet_names = wb.sheet_names()
sh = wb.sheet_by_name(sheet_names[0])
nmin = sh.row_values(6)[2]
for rownum in range(7, 27):
    temp = sh.row_values(rownum)
    nmin = min(nmin, temp[2])
print(nmin)





url = 'https://stepik.org/media/attachments/lesson/245267/salaries.xlsx'
wget.download(url)
wb = xlrd3.open_workbook('salaries.xlsx')
sheet_names = wb.sheet_names()
sheet = wb.sheet_by_name(sheet_names[0])
vals = [sheet.row_values(rownum) for rownum in range(sheet.nrows)]

max_median = 0.0
max_i = i = 1
for rec in vals[1:]:
    if (float(median(sorted(rec[1:]))) > max_median):
        max_median = float(median(sorted(rec[1:])))
        max_i = i
    i = i + 1
print(vals[max_i][0])

ave_max = 0
max_i = i = 2
for colnum in range(2, 8):
    temp = sheet.col_values(colnum)
    if mean(temp[1:]) > ave_max:
        ave_max = mean(temp[1:])
        max_i = i
    i = i + 1
print(vals[0][max_i])