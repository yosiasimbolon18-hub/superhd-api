<?php
namespace App\Services;

/**
 * Implementasi algoritma Apriori untuk Market Basket Analysis.
 */
class AprioriService
{
    private array $transactions;
    private int $totalTransaksi;
    private float $minSupport;
    private float $minConfidence;
    private int $maxItemset;

    public function __construct(array $transactions, float $minSupport = 0.1, float $minConfidence = 0.5, int $maxItemset = 3)
    {
        $this->transactions = $transactions;
        $this->totalTransaksi = count($transactions);
        $this->minSupport = $minSupport;
        $this->minConfidence = $minConfidence;
        $this->maxItemset = max(1, $maxItemset); // parameter admin, bukan hard-code lagi
    }

    private function support(array $itemset): float
    {
        if ($this->totalTransaksi === 0) return 0;
        $count = 0;
        foreach ($this->transactions as $trx) {
            if (count(array_diff($itemset, $trx)) === 0) $count++;
        }
        return $count / $this->totalTransaksi;
    }

    private function combinations(array $items, int $k): array
    {
        $result = [];
        $n = count($items);
        if ($k > $n) return $result;
        $indices = range(0, $k - 1);
        while (true) {
            $result[] = array_map(fn ($i) => $items[$i], $indices);
            $i = $k - 1;
            while ($i >= 0 && $indices[$i] === $i + $n - $k) $i--;
            if ($i < 0) break;
            $indices[$i]++;
            for ($j = $i + 1; $j < $k; $j++) $indices[$j] = $indices[$j - 1] + 1;
        }
        return $result;
    }

    public function run(): array
    {
        $allItems = [];
        foreach ($this->transactions as $trx) {
            foreach ($trx as $item) $allItems[$item] = true;
        }
        $allItems = array_keys($allItems);
        sort($allItems);

        $iterations = [];
        $frequentItemsets = [];
        $k = 1;
        $candidateItems = array_map(fn ($i) => [$i], $allItems);

        while (count($candidateItems) > 0) {
            $evaluated = [];
            foreach ($candidateItems as $itemset) {
                $sup = $this->support($itemset);
                $evaluated[] = ['items' => $itemset, 'support' => round($sup, 4)];
            }
            $passed = array_values(array_filter($evaluated, fn ($e) => $e['support'] >= $this->minSupport));

            $iterations[] = ['k' => $k, 'candidates' => $evaluated, 'frequent' => $passed];

            if (count($passed) === 0) break;
            foreach ($passed as $p) $frequentItemsets[] = array_merge($p, ['k' => $k]);

            if ($k >= $this->maxItemset) break; // batas sekarang dari parameter admin

            $items = [];
            foreach ($passed as $p) foreach ($p['items'] as $it) $items[$it] = true;
            $items = array_keys($items); sort($items);

            $nextCandidates = $this->combinations($items, $k + 1);
            $frequentSets = array_map(fn ($p) => $p['items'], $passed);
            $nextCandidates = array_values(array_filter($nextCandidates, function ($cand) use ($frequentSets, $k) {
                foreach ($this->combinations($cand, $k) as $s) {
                    $found = false;
                    foreach ($frequentSets as $fs) {
                        if (count(array_diff($s, $fs)) === 0 && count(array_diff($fs, $s)) === 0) { $found = true; break; }
                    }
                    if (!$found) return false;
                }
                return true;
            }));

            $candidateItems = $nextCandidates;
            $k++;
        }

        $rules = [];
        foreach ($frequentItemsets as $fi) {
            $items = $fi['items'];
            if (count($items) < 2) continue;
            $subsets = [];
            for ($r = 1; $r < count($items); $r++) $subsets = array_merge($subsets, $this->combinations($items, $r));
            foreach ($subsets as $antecedent) {
                $consequent = array_values(array_diff($items, $antecedent));
                if (empty($consequent)) continue;
                $supAntecedent = $this->support($antecedent);
                if ($supAntecedent == 0) continue;
                $confidence = $fi['support'] / $supAntecedent;
                if ($confidence >= $this->minConfidence) {
                    $supConsequent = $this->support($consequent);
                    $lift = $supConsequent > 0 ? $confidence / $supConsequent : 0;
                    $rules[] = [
                        'antecedent' => $antecedent, 'consequent' => $consequent,
                        'support' => round($fi['support'], 4), 'confidence' => round($confidence, 4), 'lift' => round($lift, 4),
                    ];
                }
            }
        }
        usort($rules, fn ($a, $b) => $b['confidence'] <=> $a['confidence']);

        return [
            'total_transaksi' => $this->totalTransaksi,
            'min_support' => $this->minSupport, 'min_confidence' => $this->minConfidence, 'max_itemset' => $this->maxItemset,
            'iterations' => $iterations, 'frequent_itemsets' => $frequentItemsets, 'rules' => $rules,
        ];
    }
}
