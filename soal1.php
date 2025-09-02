<?php

$jml = $_GET['jml'];
echo "<table border='1'>\n";
for ($a = $jml; $a > 0; $a--)
{
  // menghitung total dengan rumus n(n+1)/2
  // Misalnya pada url diisikan jml=4 maka a=4 maka total=4*(4+1)/2=10
  // Lanjut perulangan berikutnya jml=3 maka a=3 maka total=3*(3+1)/2=6
  // Lanjut perulangan berikutnya jml=2 maka a=2 maka total=2*(2+1)/2=3
  // Lanjut perulangan berikutnya jml=1 maka a=1 maka total=1*(1+1)/2=1
  // Berhenti di sini karena a harus > 0
  $total = $a * ($a + 1) / 2;
  echo "<tr>\n";
  echo "<td colspan='$jml'>TOTAL: $total</td>";
  echo "</tr>\n";
  
  echo "<tr>\n";
  for ($b = $a; $b > 0; $b--)
  {
    echo "<td>$b</td>";
  }
  echo "</tr>\n";
}
echo "</table>";

?>