<?php
namespace App\Services;

use DateTimeImmutable;
use RuntimeException;
use Smalot\PdfParser\Parser;

class PdfScheduleImporter
{
    private const MONTHS = [
        'janeiro'=>1, 'fevereiro'=>2, 'marco'=>3, 'abril'=>4,
        'maio'=>5, 'junho'=>6, 'julho'=>7, 'agosto'=>8,
        'setembro'=>9, 'outubro'=>10, 'novembro'=>11, 'dezembro'=>12,
    ];

    public function parse(string $path): array
    {
        $pdf = (new Parser())->parseFile($path);
        $pages = $pdf->getPages();
        if (count($pages) !== 1) {
            throw new RuntimeException('O horário deve ter uma única página.');
        }

        $text = $pages[0]->getText();
        [$year, $month] = $this->readMonth($text);
        $items = $this->items($pages[0]->getDataTm());
        $dayColumns = $this->dayColumns($items);
        $rows = $this->nurseRows($items);
        if (count($dayColumns) < (int)(new DateTimeImmutable("$year-$month-01"))->format('t') || $rows === []) {
            throw new RuntimeException('Não foi possível reconhecer a grelha do horário.');
        }

        $entries = [];
        foreach ($this->shiftItems($items) as $shift) {
            $row = $this->nearest($rows, $shift['y'], 'y');
            $day = $this->nearest($dayColumns, $shift['x'], 'x');
            if (abs($row['y'] - $shift['y']) > 12 || abs($day['x'] - $shift['x']) > 12) {
                continue;
            }
            $entries[] = [
                'pdf_name' => $row['name'],
                'day' => $day['day'],
                'date' => sprintf('%04d-%02d-%02d', $year, $month, $day['day']),
                'shift' => $shift['text'],
            ];
        }

        if ($entries === []) {
            throw new RuntimeException('O PDF não contém turnos reconhecíveis.');
        }
        return ['year'=>$year, 'month'=>$month, 'rows'=>$rows, 'entries'=>$entries, 'contacts'=>$this->contacts($text, $rows)];
    }

    private function readMonth(string $text): array
    {
        $plain = $this->normalize($text);
        foreach (self::MONTHS as $name => $number) {
            if (preg_match('/\b'.$name.'\s+(20\d{2})\b/u', $plain, $match)) {
                return [(int)$match[1], $number];
            }
        }
        throw new RuntimeException('Não foi possível identificar o mês e o ano no PDF.');
    }

    private function items(array $data): array
    {
        $items = [];
        foreach ($data as $item) {
            $value = trim((string)($item[1] ?? ''));
            if ($value === '' || !isset($item[0][4], $item[0][5])) continue;
            $items[] = ['x'=>(float)$item[0][4], 'y'=>(float)$item[0][5], 'text'=>$value];
        }
        return $items;
    }

    private function dayColumns(array $items): array
    {
        $groups = [];
        foreach ($items as $item) {
            if (preg_match('/^(?:[1-9]|[12][0-9]|3[01])$/', $item['text']) && $item['x'] > 90) {
                $key = (string)round($item['y'], 1);
                $groups[$key][] = $item;
            }
        }
        usort($groups, static fn(array $a, array $b): int => count($b) <=> count($a));
        $columns = [];
        foreach ($groups[0] ?? [] as $item) {
            $columns[] = ['day'=>(int)$item['text'], 'x'=>$item['x']];
        }
        usort($columns, static fn(array $a, array $b): int => $a['day'] <=> $b['day']);
        return $columns;
    }

    private function nurseRows(array $items): array
    {
        $groups = [];
        foreach ($items as $item) {
            if ($item['x'] < 95 && $item['y'] > 190 && $item['y'] < 490 && preg_match('/\p{L}/u', $item['text'])) {
                if (!in_array($this->normalize($item['text']), ['dias', 'horario'], true)) {
                    $key = (string)round($item['y'], 1);
                    $groups[$key][] = $item;
                }
            }
        }
        $rows = [];
        foreach ($groups as $parts) {
            usort($parts, static fn(array $a, array $b): int => $a['x'] <=> $b['x']);
            $name = '';
            foreach (array_column($parts, 'text') as $part) {
                $joinWithoutSpace = $name !== '' && preg_match('/^\p{Ll}/u', $part);
                $name .= ($name === '' || $joinWithoutSpace ? '' : ' ') . $part;
            }
            $rows[] = [
                'name' => trim($name),
                'y' => (float)$parts[0]['y'],
            ];
        }
        usort($rows, static fn(array $a, array $b): int => $b['y'] <=> $a['y']);
        return $rows;
    }

    private function shiftItems(array $items): array
    {
        $shifts = [];
        for ($i=0, $count=count($items); $i<$count; $i++) {
            $item = $items[$i];
            if ($item['x'] < 95 || $item['y'] <= 190 || $item['y'] >= 490) continue;
            $value = strtoupper($item['text']);
            if ($value === 'T' && isset($items[$i+1]) && strtoupper($items[$i+1]['text']) === 'E'
                && abs($items[$i+1]['y']-$item['y']) < .5 && $items[$i+1]['x']-$item['x'] < 10) {
                $value = 'TE'; $i++;
            }
            if (in_array($value, ['M','T','C','TE'], true)) {
                $shifts[] = ['text'=>$value, 'x'=>$item['x'], 'y'=>$item['y']];
            }
        }
        return $shifts;
    }

    private function nearest(array $items, float $value, string $axis): array
    {
        usort($items, static fn(array $a, array $b): int => abs($a[$axis]-$value) <=> abs($b[$axis]-$value));
        return $items[0];
    }

    private function contacts(string $text, array $rows): array
    {
        preg_match_all('/^\s*([\p{L}][\p{L} .]+?)\s*[–-]\s*(9\d{8})\s*$/mu', $text, $matches, PREG_SET_ORDER);
        $found = [];
        foreach ($matches as $match) $found[$this->normalize($match[1])] = $match[2];
        $aliases = [
            'elisabete simao'=>'beta', 'ana rita g'=>'ana rita gameiro',
            'viktoriia m'=>'viktoriia manziuk', 'anabela sousa'=>'anabela',
        ];
        $contacts = [];
        foreach ($rows as $row) {
            $key = $this->normalize($row['name']);
            $contactKey = $aliases[$key] ?? $key;
            $contacts[$row['name']] = $found[$contactKey] ?? null;
        }
        return $contacts;
    }

    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = strtr($value, [
            'á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a',
            'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
            'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
            'ó'=>'o','ò'=>'o','ô'=>'o','õ'=>'o','ö'=>'o',
            'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c',
        ]);
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        return trim(preg_replace('/[^a-z0-9]+/', ' ', $ascii !== false ? $ascii : $value));
    }

    public static function canonicalName(string $value): string
    {
        return [
            'ana rita g' => 'Ana Rita Gameiro',
            'viktoriia m' => 'Viktoriia Manziuk',
        ][self::normalize($value)] ?? trim($value);
    }
}
