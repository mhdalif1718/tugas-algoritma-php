<?php
/**
 * Program Kalkulator Seperti di HP
 * Kalkulator dengan antarmuka mirip kalkulator smartphone
 * Mendukung operasi dasar dan persentase
 * 
 * @author [Nama Anda]
 * @version 1.0
 */

// Inisialisasi variabel untuk session
session_start();

// Fungsi untuk menyimpan riwayat perhitungan
function simpanRiwayat($perhitungan, $hasil) {
    if (!isset($_SESSION['riwayat'])) {
        $_SESSION['riwayat'] = [];
    }
    
    // Simpan perhitungan dengan timestamp
    $data = [
        'perhitungan' => $perhitungan,
        'hasil' => $hasil,
        'waktu' => date('H:i:s')
    ];
    
    array_push($_SESSION['riwayat'], $data);
    
    // Batasi hanya 20 riwayat terakhir
    if (count($_SESSION['riwayat']) > 20) {
        array_shift($_SESSION['riwayat']);
    }
}

// Proses jika ada perhitungan dari form
$display = '';
$hasil = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['expression'])) {
        $expression = $_POST['expression'];
        
        // Hapus karakter yang tidak diinginkan untuk keamanan
        $expression = preg_replace('/[^0-9\+\-\*\/\(\)\.\%]/', '', $expression);
        
        // Evaluasi ekspresi dengan aman
        try {
            // Ganti % dengan /100
            $expression_eval = str_replace('%', '/100', $expression);
            
            // Evaluasi ekspresi
            $result = eval("return $expression_eval;");
            
            if ($result !== false && is_numeric($result)) {
                // Bulatkan hasil jika perlu
                if (is_float($result)) {
                    $result = round($result, 10);
                    // Hapus trailing zeros
                    $result = rtrim(rtrim($result, '0'), '.');
                }
                
                $display = $result;
                $hasil = $result;
                
                // Simpan riwayat
                simpanRiwayat($expression, $result);
            } else {
                $error = 'Error: Perhitungan tidak valid';
                $display = 'Error';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
            $display = 'Error';
        } catch (ParseError $e) {
            $error = 'Error: Format tidak valid';
            $display = 'Error';
        }
    } elseif (isset($_POST['clear_history'])) {
        // Hapus riwayat
        unset($_SESSION['riwayat']);
        $display = '';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator HP</title>
    <style>
        /* Reset dan dasar */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        /* Container utama kalkulator */
        .calculator-wrapper {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
            max-width: 800px;
            width: 100%;
        }
        
        /* Style kalkulator seperti HP */
        .calculator {
            background: #1a1a1a;
            border-radius: 30px;
            padding: 20px;
            width: 380px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5), 
                        0 0 0 2px rgba(255,255,255,0.1) inset;
            position: relative;
        }
        
        /* Layar kalkulator */
        .screen {
            background: #0a0a0a;
            border-radius: 15px;
            padding: 20px 25px;
            margin-bottom: 20px;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: flex-end;
            box-shadow: 0 0 20px rgba(0,0,0,0.5) inset;
            border: 1px solid #333;
        }
        
        .screen .expression {
            color: #888;
            font-size: 18px;
            min-height: 30px;
            word-break: break-all;
            text-align: right;
            width: 100%;
            transition: all 0.3s;
        }
        
        .screen .result {
            color: #fff;
            font-size: 48px;
            font-weight: 300;
            min-height: 60px;
            word-break: break-all;
            text-align: right;
            width: 100%;
            transition: all 0.3s;
        }
        
        .screen .result.error {
            color: #ff6b6b;
            font-size: 28px;
        }
        
        /* Tombol-tombol kalkulator */
        .buttons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        
        .btn {
            padding: 20px;
            font-size: 24px;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.15s ease;
            font-weight: 500;
            text-align: center;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }
        
        .btn:active {
            transform: scale(0.92);
        }
        
        /* Tombol angka */
        .btn-number {
            background: #333;
            color: #fff;
        }
        
        .btn-number:hover {
            background: #3d3d3d;
        }
        
        .btn-number:active {
            background: #444;
        }
        
        /* Tombol operator */
        .btn-operator {
            background: #ff9500;
            color: #fff;
            font-size: 28px;
        }
        
        .btn-operator:hover {
            background: #ffaa33;
        }
        
        .btn-operator:active {
            background: #e68600;
        }
        
        /* Tombol fungsi */
        .btn-function {
            background: #555;
            color: #fff;
            font-size: 20px;
        }
        
        .btn-function:hover {
            background: #666;
        }
        
        .btn-function:active {
            background: #777;
        }
        
        /* Tombol sama dengan */
        .btn-equals {
            background: #ff9500;
            color: #fff;
            font-size: 32px;
        }
        
        .btn-equals:hover {
            background: #ffaa33;
        }
        
        .btn-equals:active {
            background: #e68600;
        }
        
        /* Tombol nol (span 2 kolom) */
        .btn-zero {
            grid-column: span 2;
        }
        
        /* Riwayat perhitungan */
        .history {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 20px;
            flex: 1;
            min-width: 250px;
            max-width: 350px;
            max-height: 550px;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .history-header h3 {
            color: #333;
            font-size: 18px;
        }
        
        .history-header button {
            background: #ff6b6b;
            color: #fff;
            border: none;
            padding: 5px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .history-header button:hover {
            background: #ee5a24;
            transform: scale(1.05);
        }
        
        .history-item {
            padding: 10px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .history-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .history-item .calc {
            color: #555;
            font-size: 14px;
        }
        
        .history-item .result-hist {
            color: #333;
            font-size: 18px;
            font-weight: 600;
            color: #764ba2;
        }
        
        .history-item .time {
            color: #999;
            font-size: 11px;
            margin-top: 3px;
        }
        
        .empty-history {
            text-align: center;
            color: #999;
            padding: 30px 0;
            font-size: 14px;
        }
        
        /* Scrollbar custom */
        .history::-webkit-scrollbar {
            width: 6px;
        }
        
        .history::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .history::-webkit-scrollbar-thumb {
            background: #764ba2;
            border-radius: 10px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .calculator {
                width: 100%;
                max-width: 380px;
            }
            
            .calculator-wrapper {
                flex-direction: column;
                align-items: center;
            }
            
            .history {
                max-width: 100%;
                width: 100%;
                max-height: 300px;
            }
            
            .btn {
                padding: 18px;
                font-size: 20px;
            }
            
            .screen .result {
                font-size: 36px;
            }
        }
        
        @media (max-width: 400px) {
            .calculator {
                padding: 15px;
            }
            
            .buttons {
                gap: 8px;
            }
            
            .btn {
                padding: 15px;
                font-size: 18px;
                border-radius: 12px;
            }
            
            .screen {
                padding: 15px 20px;
                min-height: 100px;
            }
            
            .screen .result {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="calculator-wrapper">
        <!-- Kalkulator -->
        <div class="calculator" id="calculator">
            <!-- Form untuk perhitungan -->
            <form method="POST" action="" id="calcForm">
                <input type="hidden" name="expression" id="expressionInput" value="">
                
                <!-- Layar -->
                <div class="screen">
                    <div class="expression" id="displayExpression"></div>
                    <div class="result <?php echo isset($error) && $error ? 'error' : ''; ?>" id="displayResult">
                        <?php 
                        if (isset($display) && $display !== '') {
                            echo htmlspecialchars($display);
                        } else {
                            echo '0';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Tombol -->
                <div class="buttons">
                    <!-- Baris 1: Fungsi -->
                    <button type="button" class="btn btn-function" onclick="clearAll()">AC</button>
                    <button type="button" class="btn btn-function" onclick="clearLast()">⌫</button>
                    <button type="button" class="btn btn-function" onclick="addSymbol('%')">%</button>
                    <button type="button" class="btn btn-operator" onclick="addSymbol('/')">÷</button>
                    
                    <!-- Baris 2: Angka 7-9 dan operator -->
                    <button type="button" class="btn btn-number" onclick="addNumber('7')">7</button>
                    <button type="button" class="btn btn-number" onclick="addNumber('8')">8</button>
                    <button type="button" class="btn btn-number" onclick="addNumber('9')">9</button>
                    <button type="button" class="btn btn-operator" onclick="addSymbol('*')">×</button>
                    
                    <!-- Baris 3: Angka 4-6 dan operator -->
                    <button type="button" class="btn btn-number" onclick="addNumber('4')">4</button>
                    <button type="button" class="btn btn-number" onclick="addNumber('5')">5</button>
                    <button type="button" class="btn btn-number" onclick="addNumber('6')">6</button>
                    <button type="button" class="btn btn-operator" onclick="addSymbol('-')">−</button>
                    
                    <!-- Baris 4: Angka 1-3 dan operator -->
                    <button type="button" class="btn btn-number" onclick="addNumber('1')">1</button>
                    <button type="button" class="btn btn-number" onclick="addNumber('2')">2</button>
                    <button type="button" class="btn btn-number" onclick="addNumber('3')">3</button>
                    <button type="button" class="btn btn-operator" onclick="addSymbol('+')">+</button>
                    
                    <!-- Baris 5: Nol, desimal, dan sama dengan -->
                    <button type="button" class="btn btn-number btn-zero" onclick="addNumber('0')">0</button>
                    <button type="button" class="btn btn-number" onclick="addSymbol('.')">.</button>
                    <button type="button" class="btn btn-equals" onclick="calculate()">=</button>
                </div>
            </form>
        </div>
        
        <!-- Riwayat -->
        <div class="history">
            <div class="history-header">
                <h3>📝 Riwayat</h3>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="clear_history">Hapus</button>
                </form>
            </div>
            <div id="historyList">
                <?php if (isset($_SESSION['riwayat']) && !empty($_SESSION['riwayat'])): ?>
                    <?php foreach(array_reverse($_SESSION['riwayat']) as $item): ?>
                        <div class="history-item">
                            <div>
                                <div class="calc"><?php echo htmlspecialchars($item['perhitungan']); ?></div>
                                <div class="time"><?php echo $item['waktu']; ?></div>
                            </div>
                            <div class="result-hist">= <?php echo htmlspecialchars($item['hasil']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-history">Belum ada riwayat</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // State untuk kalkulator
        let currentExpression = '';
        let justCalculated = false;
        let displayElement = document.getElementById('displayExpression');
        let resultElement = document.getElementById('displayResult');
        let expressionInput = document.getElementById('expressionInput');
        
        // Fungsi untuk menambah angka
        function addNumber(num) {
            if (justCalculated) {
                // Jika baru selesai menghitung, mulai baru
                if (num === '0') {
                    return;
                }
                currentExpression = '';
                justCalculated = false;
            }
            currentExpression += num;
            updateDisplay();
        }
        
        // Fungsi untuk menambah simbol/operator
        function addSymbol(symbol) {
            if (justCalculated) {
                // Lanjutkan dari hasil perhitungan
                const lastResult = resultElement.textContent;
                if (lastResult !== '0' && lastResult !== 'Error') {
                    currentExpression = lastResult + symbol;
                } else {
                    currentExpression = '0' + symbol;
                }
                justCalculated = false;
            } else {
                // Cegah operator ganda
                const lastChar = currentExpression.slice(-1);
                if (['+', '-', '*', '/', '%', '.'].includes(lastChar)) {
                    // Ganti operator terakhir
                    currentExpression = currentExpression.slice(0, -1) + symbol;
                } else if (currentExpression !== '') {
                    currentExpression += symbol;
                } else if (symbol !== '.') {
                    currentExpression = '0' + symbol;
                } else {
                    currentExpression = '0.';
                }
            }
            updateDisplay();
        }
        
        // Fungsi untuk clear semua
        function clearAll() {
            currentExpression = '';
            justCalculated = false;
            updateDisplay();
            document.getElementById('displayResult').textContent = '0';
            document.getElementById('displayResult').className = 'result';
        }
        
        // Fungsi untuk menghapus karakter terakhir
        function clearLast() {
            if (justCalculated) {
                clearAll();
                return;
            }
            currentExpression = currentExpression.slice(0, -1);
            updateDisplay();
        }
        
        // Fungsi untuk menghitung
        function calculate() {
            if (currentExpression === '') {
                return;
            }
            
            // Cek jika ada operator di akhir
            const lastChar = currentExpression.slice(-1);
            if (['+', '-', '*', '/', '%', '.'].includes(lastChar)) {
                currentExpression = currentExpression.slice(0, -1);
            }
            
            // Kirim ke server untuk perhitungan
            expressionInput.value = currentExpression;
            document.getElementById('calcForm').submit();
        }
        
        // Fungsi untuk update tampilan
        function updateDisplay() {
            displayElement.textContent = currentExpression || '';
            expressionInput.value = currentExpression;
            
            // Preview hasil sementara (opsional)
            if (currentExpression && !justCalculated) {
                try {
                    // Hanya untuk preview, tidak benar-benar dihitung
                    const preview = currentExpression.replace('%', '/100');
                    const result = Function('"use strict"; return (' + preview + ')')();
                    if (result !== undefined && !isNaN(result) && isFinite(result)) {
                        let previewResult = result;
                        if (typeof previewResult === 'number') {
                            previewResult = parseFloat(previewResult.toFixed(10));
                        }
                        // Tidak update hasil utama, hanya untuk preview di console
                    }
                } catch (e) {
                    // Abaikan error preview
                }
            }
        }
        
        // Fungsi untuk menangani input keyboard
        document.addEventListener('keydown', function(e) {
            const key = e.key;
            
            if (key >= '0' && key <= '9') {
                e.preventDefault();
                addNumber(key);
            } else if (key === '.') {
                e.preventDefault();
                addSymbol('.');
            } else if (key === '+') {
                e.preventDefault();
                addSymbol('+');
            } else if (key === '-') {
                e.preventDefault();
                addSymbol('-');
            } else if (key === '*') {
                e.preventDefault();
                addSymbol('*');
            } else if (key === '/') {
                e.preventDefault();
                addSymbol('/');
            } else if (key === '%') {
                e.preventDefault();
                addSymbol('%');
            } else if (key === 'Enter' || key === '=') {
                e.preventDefault();
                calculate();
            } else if (key === 'Backspace') {
                e.preventDefault();
                clearLast();
            } else if (key === 'Escape' || key === 'c' || key === 'C') {
                e.preventDefault();
                clearAll();
            }
        });
        
        // Inisialisasi
        updateDisplay();
        
        // Jika ada error di server, tampilkan
        <?php if (isset($error) && $error): ?>
            resultElement.textContent = '<?php echo addslashes($error); ?>';
            resultElement.className = 'result error';
            <?php if (isset($_POST['expression'])): ?>
                currentExpression = '<?php echo addslashes($_POST['expression']); ?>';
                displayElement.textContent = currentExpression;
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>