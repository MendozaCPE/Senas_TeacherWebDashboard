<?php

namespace App\Services;

use TCPDF;

/**
 * TcPdfService
 * -----------
 * Thin wrapper around TCPDF that produces a professionally branded A4 PDF
 * for the SEÑAS Teacher Web Dashboard exports.
 *
 * Header Layout (ROW 1 + ROW 2 ONLY — no KPI content lives here):
 *  Row 1: [Logo]  SENAS / Teacher Web Dashboard   |   STUDENT PROGRESS REPORT
 *  Divider Line
 *  Row 2: GENERATED              |  TEACHER                 |  SCHOOL
 *         Aug 09, 2026 · 9:00 AM |  Christian Paul Mendoza   |  Nasugbu West Central School
 *
 * The KPI strip (Total Students / Progress Records / Lessons Completed /
 * Completion Rate / Avg Quiz Score) is rendered by summaryStrip(), which is
 * a completely separate call made by the report builder AFTER Header()
 * finishes and AFTER any sectionTitle('REPORT SUMMARY') label. It must
 * never be invoked from inside Header().
 */
class TcPdfService extends TCPDF
{
    /* Brand colours */
    private const NAVY         = [13,  50, 107];   // #0d326b
    private const NAVY_MID     = [30,  75, 143];   // #1e4b8f
    private const SLATE        = [51,  65,  85];   // #334155
    private const SLATE_LIGHT  = [148,163,184];    // #94a3b8
    private const WHITE        = [255,255,255];
    private const SOFT_BLUE    = [180,205,235];    // #b4cde7
    private const MUTED_BLUE   = [148,175,215];    // #94afd7
    private const INNER_LINE   = [35,  75, 135];
    private const BG_STRIP     = [238,243,251];
    private const BORDER       = [225,232,240];

    /**
     * Supported paper sizes (name => TCPDF format string or [w, h] mm array).
     */
    public const PAPER_SIZES = [
        'A4'     => 'A4',        // 210 × 297
        'A3'     => 'A3',        // 297 × 420
        'Letter' => 'LETTER',    // 215.9 × 279.4
        'Legal'  => 'LEGAL',     // 215.9 × 355.6
        'A5'     => 'A5',        // 148 × 210
    ];

    /**
     * Total height (mm) of the branded page-1 header (single row: "Teacher
     * Web Dashboard" left, report title true-centered on the page, metadata
     * stacked right). Public so controllers can force the cursor below it —
     * see bodyStartY().
     */
    public const HEADER_HEIGHT = 27;

    private string $reportTitle;
    private string $teacherName;
    private string $schoolName;
    private string $generatedAt;

    /** One of the keys in PAPER_SIZES */
    private string $paperSize;

    /**
     * 'every'  — branded header on every page
     * 'first'  — branded header on page 1 only (current default behaviour)
     * 'none'   — no header at all
     */
    private string $runningHeader;

    /**
     * 'footer' — "Page N of M" in the footer (current default)
     * 'none'   — suppress page numbers entirely
     */
    private string $pageNumbers;

    public function __construct(
        string $reportTitle,
        string $teacherName,
        string $schoolName,
        string $generatedAt,
        string $paperSize     = 'A4',
        string $runningHeader = 'first',
        string $pageNumbers   = 'footer'
    ) {
        $tcSize = self::PAPER_SIZES[$paperSize] ?? 'A4';

        parent::__construct('P', 'mm', $tcSize, true, 'UTF-8', false);

        $this->reportTitle   = $reportTitle;
        $this->teacherName   = $teacherName;
        $this->schoolName    = $schoolName;
        $this->generatedAt   = $generatedAt;
        $this->paperSize     = $paperSize;
        $this->runningHeader = $runningHeader;
        $this->pageNumbers   = $pageNumbers;

        $this->SetCreator('SEÑAS Teacher Web Dashboard');
        $this->SetAuthor($teacherName);
        $this->SetTitle($reportTitle);

        /* Top margin: when header runs on every page, all pages need room below
         * the header. When header is first-only or none, a compact 16 mm is fine. */
        $topMargin = ($runningHeader === 'every') ? (self::HEADER_HEIGHT + 5) : 16;
        $this->SetMargins(14, $topMargin, 14);
        $this->SetHeaderMargin(0);
        $this->SetFooterMargin(10);
        $this->SetAutoPageBreak(true, 22);

        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(...self::SLATE);
        $this->SetLineWidth(0.2);
    }

    public function Header(): void
    {
        // 'none' — skip header entirely on every page
        if ($this->runningHeader === 'none') {
            return;
        }

        // 'first' — only page 1 gets the branded header (original behaviour)
        // 'every' — all pages get the branded header
        if ($this->runningHeader === 'first' && $this->getPage() !== 1) {
            return;
        }

        $pageW  = $this->getPageWidth();
        $lm     = $this->getOriginalMargins()['left'];
        $rm     = $this->getOriginalMargins()['right'];

        /*
         * Header structure — ONE compact row, three zones (nothing else
         * lives here; the KPI cards are drawn separately by summaryStrip()):
         *
         * LEFT                    CENTER                  RIGHT
         * [Logo] SENAS             STUDENT PROGRESS        Generated: ...
         *        Teacher Web Dash. REPORT                  Teacher: ...
         *                                                  School: ...
         */
        $hdrH = self::HEADER_HEIGHT; // compact, single-row header

        /* Navy background */
        $this->SetFillColor(...self::NAVY);
        $this->Rect(0, 0, $pageW, $hdrH, 'F');

        /* ────────────────────────────────────────────
         * CENTER — Report title, TRUE center of the full page width.
         * Computed FIRST so the left/right zones can be constrained to fit
         * around it — this is what guarantees the title never shifts off
         * true center and never overlaps the metadata, no matter how wide
         * the school/teacher name gets.
         * ──────────────────────────────────────────── */
        $titleText = strtoupper($this->reportTitle);
        $titleSize = 10;
        $maxTitleWidth = $pageW * 0.36; // caps title width so both side zones keep breathing room
        $this->SetFont('helvetica', 'B', $titleSize);
        while ($this->GetStringWidth($titleText) > $maxTitleWidth && $titleSize > 7) {
            $titleSize -= 0.5;
            $this->SetFont('helvetica', 'B', $titleSize);
        }
        $titleWidth     = $this->GetStringWidth($titleText);
        $titleLeftEdge  = ($pageW / 2) - ($titleWidth / 2);
        $titleRightEdge = ($pageW / 2) + ($titleWidth / 2);
        $zoneGap        = 6;

        $this->SetXY(0, ($hdrH / 2) - ($titleSize * 0.16));
        $this->SetTextColor(...self::WHITE);
        $this->Cell($pageW, 8, $titleText, 0, 0, 'C');

        // Thin gold accent rule centered under the title — a small, quiet
        // anchor that reads as "designed" rather than a plain text block.
        $accentW = min(26, $titleWidth * 0.42);
        $accentY = ($hdrH / 2) + ($titleSize * 0.16) + 3.5;
        $this->SetDrawColor(255, 200, 0);
        $this->SetLineWidth(0.6);
        $this->Line(($pageW / 2) - ($accentW / 2), $accentY, ($pageW / 2) + ($accentW / 2), $accentY);

        /* ────────────────────────────────────────────
         * LEFT ZONE — Logo + "SENAS", vertically centered against the
         * title, constrained to end before it.
         * ──────────────────────────────────────────── */
        $leftX = $lm;
        $leftW = max(20, $titleLeftEdge - $zoneGap - $lm);

        // Logo: senya_face.png, small, left-aligned, vertically centered
        $logoPath = public_path('images/senya_face.png');
        $logoSize = 7; // mm — small icon
        $logoY    = ($hdrH / 2) - ($logoSize / 2);
        if (file_exists($logoPath)) {
            $this->Image($logoPath, $leftX, $logoY, $logoSize, $logoSize, 'PNG');
        }

        // "SENAS" text immediately to the right of the logo
        $logoGap = $logoSize + 2;
        $this->SetXY($leftX + $logoGap, $hdrH / 2 - 2);
        $this->SetFont('helvetica', 'B', 9);
        $this->SetTextColor(...self::WHITE);
        $this->setFontSpacing(0.5);
        $this->Cell($leftW - $logoGap, 4, 'SEÑAS', 0, 0, 'L');
        $this->setFontSpacing(0);



        /* ────────────────────────────────────────────
         * RIGHT ZONE — Generated / Teacher / School, stacked, right-aligned,
         * constrained to start after the title so it can never reach it.
         * ──────────────────────────────────────────── */
        $rightX = $titleRightEdge + $zoneGap;
        $rightW = max(30, ($pageW - $rm) - $rightX);
        $metaLines = [
            ['label' => 'Generated: ', 'value' => $this->generatedAt],
            ['label' => 'Teacher: ',   'value' => $this->teacherName],
            ['label' => 'School: ',    'value' => $this->schoolName],
        ];
        $lineH  = 4.8;
        $metaY0 = ($hdrH / 2) - (($lineH * count($metaLines)) / 2);

        foreach ($metaLines as $i => $line) {
            $y = $metaY0 + ($i * $lineH);

            /* Measure both parts at their own fonts */
            $this->SetFont('helvetica', 'B', 6.5);
            $this->setFontSpacing(0.2);
            $labelW = $this->GetStringWidth($line['label']) + (0.2 * strlen($line['label'])) + 1.5;
            $this->setFontSpacing(0);

            $this->SetFont('helvetica', '', 7);
            // Truncate long values to fit within the right zone
            $maxValW = $rightW - $labelW;
            $value = $line['value'];
            while ($this->GetStringWidth($value) > $maxValW && strlen($value) > 3) {
                $value = substr($value, 0, -1);
            }
            if ($value !== $line['value']) {
                $value = rtrim(substr($value, 0, -1)) . '…';
            }
            $valueW = $this->GetStringWidth($value) + 1;

            /* Pin the label+value pair flush to the right edge of the zone.
             * pairX = rightX + rightW - labelW - valueW  */
            $pairX = $rightX + $rightW - $labelW - $valueW;

            /* Label */
            $this->SetXY($pairX, $y);
            $this->SetFont('helvetica', 'B', 6.5);
            $this->setFontSpacing(0.2);
            $this->SetTextColor(...self::MUTED_BLUE);
            $this->Cell($labelW, $lineH, $line['label'], 0, 0, 'L');
            $this->setFontSpacing(0);

            /* Value — immediately after label, no gap */
            $this->SetFont('helvetica', '', 7);
            $this->SetTextColor(...self::WHITE);
            $this->Cell($valueW, $lineH, $value, 0, 0, 'L');
        }

        /* Bottom accent line — this is the hard edge of the header. Anything
         * drawn after this point (section titles, KPI strip, tables) belongs
         * to the BODY, never the header. */
        $this->SetDrawColor(...self::NAVY_MID);
        $this->SetLineWidth(0.5);
        $this->Line(0, $hdrH, $pageW, $hdrH);

        /* Push body content start below header with breathing room */
        $this->SetY($hdrH + 5);

        /* Reset body styles */
        $this->SetTextColor(...self::SLATE);
        $this->SetDrawColor(...self::BORDER);
        $this->SetLineWidth(0.2);
        $this->SetFont('helvetica', '', 9);
    }

    /**
     * Y position (mm) where body content should start on page 1, below the
     * branded header. TCPDF restores the cursor to the top margin after
     * Header() returns control to AddPage(), which discards Header()'s own
     * SetY() call — so controllers MUST call this explicitly right after
     * AddPage(), e.g.:
     *
     *   $pdf->AddPage();
     *   $pdf->SetY($pdf->bodyStartY());
     *
     * When runningHeader = 'none', HEADER_HEIGHT is still used as a small
     * top-padding so content doesn't hug the very top of the page.
     */
    public function bodyStartY(): float
    {
        return self::HEADER_HEIGHT + 5;
    }

    public function Footer(): void
    {
        $pageW  = $this->getPageWidth();
        $lm     = $this->getOriginalMargins()['left'];
        $rm     = $this->getOriginalMargins()['right'];
        $usable = $pageW - $lm - $rm;

        $this->SetY(-16);
        $this->SetDrawColor(...self::BORDER);
        $this->SetLineWidth(0.25);
        $this->Line($lm, $this->GetY(), $pageW - $rm, $this->GetY());

        $this->SetY(-14);
        $this->SetX($lm);
        $this->SetFont('helvetica', 'B', 7);
        $this->SetTextColor(...self::NAVY);
        $this->Cell(14, 5, 'SEÑAS', 0, 0, 'L');

        $this->SetFont('helvetica', '', 7);
        $this->SetTextColor(...self::SLATE_LIGHT);
        $this->Cell(80, 5, 'Teacher Web Dashboard - Confidential Academic Report', 0, 0, 'L');

        // Only show page number when pageNumbers option is 'footer' (default)
        if ($this->pageNumbers === 'footer') {
            $this->SetX($lm);
            $this->Cell($usable, 5, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'R');
        }
    }

    public function sectionTitle(string $label): void
    {
        $lm     = $this->getOriginalMargins()['left'];
        $rm     = $this->getOriginalMargins()['right'];
        $usable = $this->getPageWidth() - $lm - $rm;

        $this->SetFont('helvetica', 'B', 7.5);
        $this->SetTextColor(...self::SLATE_LIGHT);
        $this->Cell($usable, 5, strtoupper($label), 0, 1, 'L');

        $this->SetDrawColor(...self::BORDER);
        $this->SetLineWidth(0.25);
        $y = $this->GetY();
        $this->Line($lm, $y, $lm + $usable, $y);
        $this->Ln(4);

        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(...self::SLATE);
    }

    /**
     * KPI strip — Total Students / Progress Records / Lessons Completed /
     * Completion Rate / Avg Quiz Score. This is DELIBERATELY separate from
     * Header(). Call it from the report builder after the header/page has
     * rendered, e.g.:
     *
     *   $pdf->AddPage();
     *   $pdf->sectionTitle('Report Summary');
     *   $pdf->summaryStrip($stats);
     *
     * Never call this from inside Header() — doing so is what causes the
     * KPI cards to visually fuse with the navy header.
     */
    public function summaryStrip(array $stats): void
    {
        $lm     = $this->getOriginalMargins()['left'];
        $rm     = $this->getOriginalMargins()['right'];
        $usable = $this->getPageWidth() - $lm - $rm;
        $n      = max(1, count($stats));
        $colW   = $usable / $n;
        $y0     = $this->GetY();
        $stripH = 22;

        $this->SetFillColor(248, 250, 255);
        $this->RoundedRect($lm, $y0, $usable, $stripH, 2, '1111', 'F');
        $this->SetDrawColor(...self::BORDER);
        $this->SetLineWidth(0.3);
        $this->RoundedRect($lm, $y0, $usable, $stripH, 2, '1111', 'D');

        $valFontSz   = ($n >= 6) ? 13 : 15;
        $labelFontSz = ($n >= 6) ? 5.8 : 6.5;

        foreach ($stats as $i => $stat) {
            $cx = $lm + $i * $colW;
            if ($i > 0) {
                $this->SetDrawColor(226, 232, 240);
                $this->Line($cx, $y0 + 3, $cx, $y0 + $stripH - 3);
            }
            $this->SetFont('helvetica', 'B', $valFontSz);
            $this->SetTextColor(...self::NAVY);
            $this->SetXY($cx + 0.5, $y0 + 3.5);
            $this->Cell($colW - 1, 7.5, (string)$stat['value'], 0, 0, 'C');

            $this->SetFont('helvetica', 'B', $labelFontSz);
            $this->SetTextColor(...self::SLATE_LIGHT);
            $this->SetXY($cx + 0.5, $y0 + 12.5);
            $this->Cell($colW - 1, 5, strtoupper((string)$stat['label']), 0, 0, 'C');
        }

        $this->SetXY($lm, $y0 + $stripH);
        $this->Ln(4);
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(...self::SLATE);
    }

    public function dataTable(array $headers, array $rows): void
    {
        $lm   = $this->getOriginalMargins()['left'];
        $rowH = 7.5;

        $this->SetFillColor(...self::NAVY);
        $this->SetTextColor(...self::WHITE);
        $this->SetFont('helvetica', 'B', 7);
        $this->SetDrawColor(...self::NAVY);
        $this->SetX($lm);

        foreach ($headers as $h) {
            $this->Cell($h['width'], $rowH, strtoupper($h['label']), 1, 0, $h['align'] ?? 'L', true);
        }
        $this->Ln();

        $this->SetFont('helvetica', '', 8.5);
        $this->SetDrawColor(...self::BORDER);
        $fillRow = false;

        foreach ($rows as $row) {
            if ($this->GetY() + $rowH > $this->getPageHeight() - 28) {
                $this->AddPage();
                $this->SetFillColor(...self::NAVY);
                $this->SetTextColor(...self::WHITE);
                $this->SetFont('helvetica', 'B', 7);
                $this->SetDrawColor(...self::NAVY);
                $this->SetX($lm);
                foreach ($headers as $h) {
                    $this->Cell($h['width'], $rowH, strtoupper($h['label']), 1, 0, $h['align'] ?? 'L', true);
                }
                $this->Ln();
                $this->SetFont('helvetica', '', 8.5);
                $this->SetDrawColor(...self::BORDER);
                $fillRow = false;
            }

            $bg = $fillRow ? [248, 252, 255] : [255, 255, 255];
            $this->SetFillColor(...$bg);
            $this->SetTextColor(...self::SLATE);
            $this->SetX($lm);

            foreach ($headers as $i => $h) {
                $val = $row[$i] ?? '';
                $this->Cell($h['width'], $rowH, (string) $val, 'B', 0, $h['align'] ?? 'L', true);
            }
            $this->Ln();
            $fillRow = !$fillRow;
        }

        $this->Ln(4);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(...self::SLATE);
    }

    public function studentBand(string $name, string $grade, int $completed, int $total, float $pct, int $quizzes, float $avgScore, float $gestureAccuracy = 0): void
    {
        $lm     = $this->getOriginalMargins()['left'];
        $rm     = $this->getOriginalMargins()['right'];
        $usable = $this->getPageWidth() - $lm - $rm;

        if ($this->GetY() + 14 > $this->getPageHeight() - 28) {
            $this->AddPage();
        }

        $y = $this->GetY();
        $this->SetFillColor(241, 246, 254);
        $this->RoundedRect($lm, $y, $usable, 12, 1.5, '1111', 'F');
        $this->SetDrawColor(...self::BORDER);
        $this->SetLineWidth(0.3);
        $this->RoundedRect($lm, $y, $usable, 12, 1.5, '1111', 'D');

        $this->SetFont('helvetica', 'B', 9.5);
        $this->SetTextColor(...self::NAVY);
        $this->SetXY($lm + 3, $y + 1.5);
        $this->Cell(65, 5, $name, 0, 0, 'L');

        $this->SetFont('helvetica', '', 7.5);
        $this->SetTextColor(...self::SLATE_LIGHT);
        $this->SetXY($lm + 3, $y + 6.8);
        $this->Cell(65, 4, $grade, 0, 0, 'L');

        $this->SetFont('helvetica', 'B', 7.5);
        $this->SetTextColor(30, 75, 143);
        $statsStr = $completed . '/' . $total . ' Lessons    ' . $pct . '% Complete    ' . $quizzes . ' Quiz' . ($quizzes !== 1 ? 'zes' : '') . '    Avg ' . number_format($avgScore, 1) . ' pts';
        if ($gestureAccuracy > 0) {
            $statsStr .= '    ' . number_format($gestureAccuracy, 1) . '% Gesture Acc';
        }
        $this->SetXY($lm + 65, $y + 3.5);
        $this->Cell($usable - 68, 5, $statsStr, 0, 0, 'R');

        $this->SetXY($lm, $y + 12);
        $this->Ln(1);
    }

    public function moduleBand(string $title): void
    {
        $lm     = $this->getOriginalMargins()['left'];
        $rm     = $this->getOriginalMargins()['right'];
        $usable = $this->getPageWidth() - $lm - $rm;

        $y = $this->GetY();
        $this->SetFillColor(241, 245, 249);
        $this->Rect($lm, $y, $usable, 6.5, 'F');
        $this->SetDrawColor(203, 213, 225);
        $this->SetLineWidth(0.2);
        $this->Line($lm, $y + 6.5, $lm + $usable, $y + 6.5);

        $this->SetFont('helvetica', 'B', 7.5);
        $this->SetTextColor(...self::NAVY);
        $this->SetXY($lm + 3, $y + 1.2);
        $this->Cell($usable - 6, 4.5, 'MODULE: ' . strtoupper($title), 0, 1, 'L');
    }

    public function lessonRow(
        string $title,
        bool $aiGenerated,
        string $difficulty,
        bool $started,
        bool $completed,
        bool $quizDone,
        ?float $quizScore,
        string $lastAccessed,
        bool $evenRow
    ): void {
        $lm   = $this->getOriginalMargins()['left'];
        $rowH = 7;

        if ($this->GetY() + $rowH > $this->getPageHeight() - 28) {
            $this->AddPage();
        }

        $bg = $evenRow ? [248, 252, 255] : [255, 255, 255];
        $this->SetFillColor(...$bg);

        $this->SetFont('helvetica', 'B', 8.5);
        $this->SetTextColor(30, 41, 59);
        $this->SetX($lm);
        $label = strlen($title) > 45 ? substr($title, 0, 42) . '...' : $title;
        if ($aiGenerated) $label .= ' [AI]';
        $this->Cell(75, $rowH, $label, 'B', 0, 'L', true);

        $this->SetFont('helvetica', '', 8.5);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(28, $rowH, ucfirst($difficulty), 'B', 0, 'L', true);

        if (!$started) {
            $statusText = 'Not Started';
            $this->SetTextColor(148, 163, 184);
        } elseif ($completed) {
            $statusText = 'Completed';
            $this->SetTextColor(...self::NAVY);
        } else {
            $statusText = 'In Progress';
            $this->SetTextColor(26, 111, 212);
        }
        $this->SetFont('helvetica', 'B', 7.5);
        $this->Cell(32, $rowH, $statusText, 'B', 0, 'L', true);

        $this->SetFont('helvetica', 'B', 8.5);
        if ($quizDone) {
            $this->SetTextColor(...self::NAVY);
            $this->Cell(24, $rowH, number_format((float)$quizScore, 0) . ' pts', 'B', 0, 'C', true);
        } elseif ($started) {
            $this->SetFont('helvetica', '', 8);
            $this->SetTextColor(...self::SLATE_LIGHT);
            $this->Cell(24, $rowH, 'Pending', 'B', 0, 'C', true);
        } else {
            $this->SetTextColor(203, 213, 225);
            $this->Cell(24, $rowH, '-', 'B', 0, 'C', true);
        }

        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, $rowH, $lastAccessed, 'B', 1, 'L', true);

        $this->SetTextColor(...self::SLATE);
    }

    public function barRow(string $label, float $value, float $maxValue = 100, array $barColor = null): void
    {
        $barColor = $barColor ?? self::NAVY_MID;
        $lm      = $this->getOriginalMargins()['left'];
        $rm      = $this->getOriginalMargins()['right'];
        $usable  = $this->getPageWidth() - $lm - $rm;
        $labelW  = $usable * 0.40;
        $barW    = $usable * 0.48;
        $valW    = $usable * 0.12;
        $rowH    = 7;
        $barH    = 3.5;

        $this->SetFont('helvetica', '', 8.5);
        $this->SetTextColor(...self::SLATE);
        $this->SetX($lm);
        $truncLabel = strlen($label) > 32 ? substr($label, 0, 29) . '...' : $label;
        $this->Cell($labelW, $rowH, $truncLabel, 'B', 0, 'L');

        $barY = $this->GetY() + ($rowH - $barH) / 2;
        $barX = $lm + $labelW;
        $this->SetFillColor(238, 242, 247);
        $this->RoundedRect($barX, $barY, $barW, $barH, 1.5, '1111', 'F');

        $fillW = $maxValue > 0 ? max(2, ($value / $maxValue) * $barW) : 2;
        $this->SetFillColor(...$barColor);
        $this->RoundedRect($barX, $barY, $fillW, $barH, 1.5, '1111', 'F');

        $this->SetX($barX + $barW);
        $this->SetFont('helvetica', 'B', 8.5);
        $this->SetTextColor(...self::NAVY);
        $this->Cell($valW, $rowH, number_format($value, 1) . '%', 'B', 1, 'R');

        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(...self::SLATE);
    }

    /* ═══════════════════════════════════════════════════════════════════
     * NEW — added to match the web dashboard's "Class Progress Over Time"
     * line chart and "Module Difficulty Ranking" panels in the PDF export.
     * ═══════════════════════════════════════════════════════════════════ */

    /**
     * Sample a cubic Bézier curve into a flat array of [x,y] points.
     * Mirrors the same control-point math the web blade uses to build its
     * SVG "C c1x,c1y c2x,c2y x,y" path segments (dx = (p1.x - p0.x) / 2).
     */
    private function bezierSegmentPoints(array $p0, array $p1, int $steps = 14): array
    {
        $dx  = ($p1['x'] - $p0['x']) / 2;
        $c1  = ['x' => $p0['x'] + $dx, 'y' => $p0['y']];
        $c2  = ['x' => $p1['x'] - $dx, 'y' => $p1['y']];

        $pts = [];
        for ($i = 0; $i <= $steps; $i++) {
            $t = $i / $steps;
            $mt = 1 - $t;
            $x = ($mt ** 3) * $p0['x'] + 3 * ($mt ** 2) * $t * $c1['x'] + 3 * $mt * ($t ** 2) * $c2['x'] + ($t ** 3) * $p1['x'];
            $y = ($mt ** 3) * $p0['y'] + 3 * ($mt ** 2) * $t * $c1['y'] + 3 * $mt * ($t ** 2) * $c2['y'] + ($t ** 3) * $p1['y'];
            $pts[] = ['x' => $x, 'y' => $y];
        }
        return $pts;
    }

    /**
     * "Class Progress Over Time" — visual match of the web panel:
     * panel title/subtitle + count badge, dashed gridlines at 0/25/50/75/100
     * with axis labels, gradient-look area fill (alpha-blended navy), smooth
     * navy curve, point markers (last point highlighted), x-axis labels,
     * current-value badge top-right. Auto page-breaks if it won't fit.
     *
     * Call from the report builder, e.g.:
     *
     *   $pdf->sectionTitle('Class Progress');
     *   $pdf->progressLineChart(
     *       $data['progressOverTime'],
     *       ucfirst($period) . ' average quiz score',
     *       count($data['progressOverTime']) . ' ' . $period
     *   );
     *
     * @param array  $points      [['label' => 'Jun 02', 'value' => 82.4], ...]
     *                             (same shape as $progressOverTime in the controller)
     * @param string $periodLabel e.g. "Weekly average quiz score 2026"
     * @param string $countLabel  e.g. "8 weekly"
     */
    public function progressLineChart(array $points, string $periodLabel, string $countLabel): void
    {
        $lm     = $this->getOriginalMargins()['left'];
        $rm     = $this->getOriginalMargins()['right'];
        $usable = $this->getPageWidth() - $lm - $rm;

        $panelH = 80; // reserved height for header + chart
        if ($this->GetY() + $panelH > $this->getPageHeight() - 28) {
            $this->AddPage();
        }

        $panelY = $this->GetY();

        /* Panel card background (matches .panel styling) */
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(...self::BORDER);
        $this->SetLineWidth(0.25);
        $this->RoundedRect($lm, $panelY, $usable, $panelH, 2, '1111', 'FD');

        $padX = 6;

        /* Header row: title/subtitle left, count badge right */
        $this->SetFont('helvetica', 'B', 10.5);
        $this->SetTextColor(...self::NAVY);
        $this->SetXY($lm + $padX, $panelY + 5);
        $this->Cell($usable - (2 * $padX) - 32, 5, 'Class Progress Over Time', 0, 0, 'L');

        $this->SetFont('helvetica', 'B', 6.5);
        $this->SetTextColor(...self::SLATE_LIGHT);
        $this->SetFillColor(241, 245, 249);
        $this->SetXY($lm + $usable - $padX - 32, $panelY + 5);
        $this->Cell(32, 5, strtoupper($countLabel), 0, 0, 'C', true);

        $this->SetXY($lm + $padX, $panelY + 10.5);
        $this->SetFont('helvetica', '', 7.5);
        $this->SetTextColor(...self::SLATE_LIGHT);
        $this->Cell($usable - (2 * $padX), 4, $periodLabel, 0, 0, 'L');

        $chartTop = $panelY + 18;
        $chartH   = $panelH - 22;

        if (empty($points)) {
            $this->SetFont('helvetica', '', 9);
            $this->SetTextColor(...self::SLATE_LIGHT);
            $this->SetXY($lm, $chartTop + ($chartH / 2) - 3);
            $this->Cell($usable, 6, 'No progress data available yet.', 0, 0, 'C');
            $this->SetY($panelY + $panelH + 6);
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor(...self::SLATE);
            return;
        }

        /* Plot area, inset within the card */
        $padL   = $lm + 12;
        $padR   = $lm + $usable - 4;
        $plotW  = $padR - $padL;
        $plotT  = $chartTop + 2;
        $plotB  = $chartTop + $chartH - 6;
        $plotH  = $plotB - $plotT;
        $count  = count($points);

        /* Gridlines + Y labels (0 / 25 / 50 / 75 / 100) */
        $this->SetFont('helvetica', '', 6);
        foreach ([0, 25, 50, 75, 100] as $gv) {
            $gy = $plotB - ($gv / 100) * $plotH;
            $this->SetDrawColorArray([232, 236, 240]);
            $this->SetLineStyle(['width' => 0.15, 'dash' => '1,1', 'color' => [232, 236, 240]]);
            $this->Line($padL, $gy, $padR, $gy);
            $this->SetTextColor(148, 163, 184);
            $this->SetXY($lm, $gy - 1.6);
            $this->Cell(10, 3, $gv . '%', 0, 0, 'L');
        }
        $this->SetLineStyle(['width' => 0.2, 'dash' => 0]);

        /* Map data points into plot coordinates */
        $pts = [];
        foreach ($points as $i => $p) {
            $x = $count > 1 ? $padL + ($i / ($count - 1)) * $plotW : $padL + $plotW / 2;
            $y = $plotB - (max(0, min(100, $p['value'])) / 100) * $plotH;
            $pts[] = ['x' => $x, 'y' => $y, 'value' => $p['value'], 'label' => $p['label']];
        }

        /* Sample the full smooth curve (matches SVG's cubic C-path) */
        $curve = [];
        for ($i = 0; $i < count($pts) - 1; $i++) {
            $seg = $this->bezierSegmentPoints($pts[$i], $pts[$i + 1]);
            if ($i > 0) array_shift($seg); // avoid duplicate joint point
            $curve = array_merge($curve, $seg);
        }
        if (empty($curve)) $curve = [$pts[0]]; // single-point case

        /* Area fill under the curve — alpha-blended navy, mirrors the SVG gradient */
        $areaPoly = [];
        foreach ($curve as $p) { $areaPoly[] = $p['x']; $areaPoly[] = $p['y']; }
        $areaPoly[] = end($curve)['x']; $areaPoly[] = $plotB;
        $areaPoly[] = $curve[0]['x'];   $areaPoly[] = $plotB;

        $this->SetAlpha(0.14);
        $this->SetFillColor(26, 111, 212);
        $this->Polygon($areaPoly, 'F');
        $this->SetAlpha(1);

        /* The curve line itself */
        $linePts = [];
        foreach ($curve as $p) { $linePts[] = $p['x']; $linePts[] = $p['y']; }
        $this->PolyLine($linePts, 'D', ['width' => 0.7, 'color' => [13, 50, 107], 'cap' => 'round', 'join' => 'round']);

        /* Point markers — last point highlighted, like the web version */
        foreach ($pts as $i => $p) {
            $isLast = ($i === count($pts) - 1);
            $r = $isLast ? 1.3 : 1.0;
            $this->SetFillColor(...($isLast ? [26, 111, 212] : self::NAVY));
            $this->SetDrawColor(255, 255, 255);
            $this->SetLineWidth(0.4);
            $this->Circle($p['x'], $p['y'], $r, 0, 360, 'FD');
        }

        /* X-axis labels — thinned out like the web (max ~10 labels) */
        $labelStep = max(1, (int) floor($count / 8));
        $this->SetFont('helvetica', '', 6);
        $this->SetTextColor(148, 163, 184);
        foreach ($pts as $i => $p) {
            if ($i % $labelStep !== 0 && $i !== $count - 1) continue;
            $this->SetXY($p['x'] - 8, $plotB + 2);
            $this->Cell(16, 3, $p['label'], 0, 0, 'C');
        }

        /* Current-value badge — floats above the last data point, matching the
         * web panel's tooltip bubble. Anchored to the last point's X so it
         * never collides with the count badge in the panel header. */
        $lastVal  = number_format(end($pts)['value'], 1) . '%';
        $lastPt   = end($pts);
        $this->SetFont('helvetica', 'B', 8);
        $badgeW   = $this->GetStringWidth($lastVal) + 6;
        $badgeH   = 6;
        // Center the badge horizontally on the last point; clamp so it
        // never bleeds outside the right edge of the plot area.
        $badgeX   = min($lastPt['x'] - ($badgeW / 2), $padR - $badgeW);
        $badgeY   = $lastPt['y'] - $badgeH - 3; // 3 mm gap above the marker
        // Clamp so it never overlaps the panel title row (top 16 mm of panel).
        $badgeY   = max($panelY + 16, $badgeY);
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(...self::BORDER);
        $this->SetLineWidth(0.2);
        $this->RoundedRect($badgeX, $badgeY, $badgeW, $badgeH, 3, '1111', 'FD');
        $this->SetTextColor(...self::NAVY);
        $this->SetXY($badgeX, $badgeY + 1);
        $this->Cell($badgeW, 4, $lastVal, 0, 0, 'C');

        $this->SetY($panelY + $panelH + 6);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(...self::SLATE);
        $this->SetLineWidth(0.2);
    }

    /**
     * "Module Difficulty Ranking" — visual match of the web panel: title +
     * "Hardest first" badge, then each lesson as a label/score row with a
     * gradient-style progress bar underneath (lowest average score first,
     * same ordering the controller already returns).
     *
     * Call from the report builder, e.g.:
     *
     *   $pdf->sectionTitle('Module Difficulty');
     *   $pdf->moduleDifficultyList($data['lessonDifficulty']);
     *
     * @param \Illuminate\Support\Collection|array $lessons  each item:
     *        ['title' => ..., 'avg_score' => float, 'attempts' => int]
     * @param int $maxRows  cap rows drawn on the PDF (web scrolls; PDF can't)
     */
    public function moduleDifficultyList($lessons, int $maxRows = 8): void
    {
        $lm     = $this->getOriginalMargins()['left'];
        $rm     = $this->getOriginalMargins()['right'];
        $usable = $this->getPageWidth() - $lm - $rm;

        $lessons = collect($lessons)->take($maxRows);
        $rowH    = 11;
        $panelH  = 16 + ($lessons->count() > 0 ? $lessons->count() * $rowH : 14);

        if ($this->GetY() + $panelH > $this->getPageHeight() - 28) {
            $this->AddPage();
        }

        $panelY = $this->GetY();
        $padX   = 6;

        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(...self::BORDER);
        $this->SetLineWidth(0.25);
        $this->RoundedRect($lm, $panelY, $usable, $panelH, 2, '1111', 'FD');

        $this->SetFont('helvetica', 'B', 10.5);
        $this->SetTextColor(...self::NAVY);
        $this->SetXY($lm + $padX, $panelY + 5);
        $this->Cell($usable - (2 * $padX) - 28, 5, 'Module Difficulty Ranking', 0, 0, 'L');

        $this->SetFont('helvetica', 'B', 6.5);
        $this->SetTextColor(...self::SLATE_LIGHT);
        $this->SetFillColor(241, 245, 249);
        $this->SetXY($lm + $usable - $padX - 28, $panelY + 5);
        $this->Cell(28, 5, 'HARDEST FIRST', 0, 0, 'C', true);

        $this->SetXY($lm + $padX, $panelY + 10.5);
        $this->SetFont('helvetica', '', 7.5);
        $this->SetTextColor(...self::SLATE_LIGHT);
        $this->Cell($usable - (2 * $padX), 4, 'Lessons ordered lowest to highest average score', 0, 0, 'L');

        $listTop = $panelY + 17;

        if ($lessons->isEmpty()) {
            $this->SetFont('helvetica', '', 9);
            $this->SetTextColor(...self::SLATE_LIGHT);
            $this->SetXY($lm, $listTop);
            $this->Cell($usable, 8, 'No lesson score data available yet.', 0, 0, 'C');
            $this->SetY($panelY + $panelH + 6);
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor(...self::SLATE);
            return;
        }

        $rowW  = $usable - (2 * $padX);
        $barY0 = 6.5; // offset of bar within each row
        $barH  = 2.2;

        foreach ($lessons as $i => $lesson) {
            $rowTop = $listTop + ($i * $rowH);

            $title = (string) $lesson['title'];
            $score = (float) $lesson['avg_score'];
            $trunc = strlen($title) > 60 ? substr($title, 0, 57) . '...' : $title;

            $this->SetFont('helvetica', 'B', 8.5);
            $this->SetTextColor(...self::NAVY);
            $this->SetXY($lm + $padX, $rowTop);
            $this->Cell($rowW - 16, 4, $trunc, 0, 0, 'L');

            $this->SetFont('helvetica', 'B', 8);
            $this->SetTextColor(...self::SLATE_LIGHT);
            $this->SetXY($lm + $padX + $rowW - 16, $rowTop);
            $this->Cell(16, 4, number_format($score, 1) . '%', 0, 0, 'R');

            /* Track */
            $this->SetFillColor(238, 242, 247);
            $this->RoundedRect($lm + $padX, $rowTop + $barY0, $rowW, $barH, 1, '1111', 'F');

            /* Fill — light-to-navy blend approximated with the mid-tone navy */
            $fillW = max(2, ($score / 100) * $rowW);
            $this->SetFillColor(...self::NAVY_MID);
            $this->RoundedRect($lm + $padX, $rowTop + $barY0, $fillW, $barH, 1, '1111', 'F');

            if ($i < $lessons->count() - 1) {
                $this->SetDrawColor(248, 250, 252);
                $this->SetLineWidth(0.15);
                $this->Line($lm + $padX, $rowTop + $rowH - 1.5, $lm + $usable - $padX, $rowTop + $rowH - 1.5);
            }
        }

        $this->SetY($panelY + $panelH + 6);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(...self::SLATE);
        $this->SetLineWidth(0.2);
    }

    public function insightBox(string $title, string $body, string $theme = 'gold'): void
    {
        $lm     = $this->getOriginalMargins()['left'];
        $rm     = $this->getOriginalMargins()['right'];
        $usable = $this->getPageWidth() - $lm - $rm;

        $startY = $this->GetY();
        $this->startTransaction();
        $this->SetFont('helvetica', '', 8.5);
        $this->SetX($lm + 6);
        $this->MultiCell($usable - 10, 4.5, $body, 0, 'L');
        $endY = $this->GetY();
        $this->rollbackTransaction(true);

        $boxH = max(18, $endY - $startY + 9);

        if ($theme === 'gold') {
            // Warm Gold / Amber Insight styling (matches senya-insight-gold on web)
            $this->SetFillColor(255, 251, 235); // #fffbeb
            $this->RoundedRect($lm, $startY, $usable, $boxH, 1.5, '1111', 'F');
            $this->SetDrawColor(254, 243, 199); // #fef3c7
            $this->SetLineWidth(0.3);
            $this->RoundedRect($lm, $startY, $usable, $boxH, 1.5, '1111', 'D');

            // Gold left indicator bar
            $this->SetFillColor(217, 119, 6); // #d97706
            $this->RoundedRect($lm, $startY, 3, $boxH, 1, '1001', 'F');

            // Title
            $this->SetFont('helvetica', 'B', 8.5);
            $this->SetTextColor(146, 64, 14); // #92400e
            $this->SetXY($lm + 6, $startY + 3);
            $this->Cell($usable - 9, 4.5, strtoupper($title), 0, 1, 'L');

            // Body
            $this->SetFont('helvetica', '', 8);
            $this->SetTextColor(120, 53, 15); // #78350f
            $this->SetX($lm + 6);
            $this->MultiCell($usable - 9, 4.2, $body, 0, 'L');
        } else {
            // Navy / Blue styling
            $this->SetFillColor(240, 249, 255);
            $this->RoundedRect($lm, $startY, $usable, $boxH, 1.5, '1111', 'F');
            $this->SetDrawColor(224, 242, 254);
            $this->SetLineWidth(0.3);
            $this->RoundedRect($lm, $startY, $usable, $boxH, 1.5, '1111', 'D');

            $this->SetFillColor(26, 111, 212);
            $this->RoundedRect($lm, $startY, 3, $boxH, 1, '1001', 'F');

            $this->SetFont('helvetica', 'B', 8.5);
            $this->SetTextColor(...self::NAVY);
            $this->SetXY($lm + 6, $startY + 3);
            $this->Cell($usable - 9, 4.5, strtoupper($title), 0, 1, 'L');

            $this->SetFont('helvetica', '', 8);
            $this->SetTextColor(71, 85, 105);
            $this->SetX($lm + 6);
            $this->MultiCell($usable - 9, 4.2, $body, 0, 'L');
        }

        $this->SetY($startY + $boxH + 4);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(...self::SLATE);
    }

    public function getTwoColLayout(): array
    {
        $lm    = $this->getOriginalMargins()['left'];
        $rm    = $this->getOriginalMargins()['right'];
        $total = $this->getPageWidth() - $lm - $rm;
        $gap   = 6;
        $colW  = ($total - $gap) / 2;
        return [
            'leftX'  => $lm,
            'rightX' => $lm + $colW + $gap,
            'colW'   => $colW,
        ];
    }

    public function download(string $filename): \Illuminate\Http\Response
    {
        $content = $this->Output($filename, 'S');
        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => strlen($content),
        ]);
    }
}