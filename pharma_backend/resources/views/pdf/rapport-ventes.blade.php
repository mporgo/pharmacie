<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 11px;
      color: #333;
    }

    .header {
      background-color: #1A3C6B;
      color: white;
      padding: 20px 24px;
      margin-bottom: 20px;
    }

    .header h1 { font-size: 20px; font-weight: bold; }
    .header p  { font-size: 11px; color: #B8D4F0; margin-top: 4px; }

    .meta {
      display: flex;
      justify-content: space-between;
      background: #EBF3FB;
      padding: 10px 24px;
      margin-bottom: 20px;
      border-left: 4px solid #2E75B6;
    }

    .meta-item { text-align: center; }
    .meta-item .val { font-size: 16px; font-weight: bold; color: #1A3C6B; }
    .meta-item .lbl { font-size: 10px; color: #666; }

    .section-title {
      background: #1A3C6B;
      color: white;
      padding: 6px 12px;
      font-weight: bold;
      font-size: 12px;
      margin: 0 24px 10px 24px;
    }

    table {
      width: calc(100% - 48px);
      margin: 0 24px 20px 24px;
      border-collapse: collapse;
    }

    thead tr {
      background-color: #2E75B6;
      color: white;
    }

    thead th {
      padding: 7px 8px;
      text-align: left;
      font-size: 10px;
    }

    tbody tr:nth-child(even) { background-color: #EBF3FB; }
    tbody tr:nth-child(odd)  { background-color: #FFFFFF; }

    tbody td {
      padding: 6px 8px;
      font-size: 10px;
      border-bottom: 1px solid #D6E4F0;
    }

    tfoot tr {
      background-color: #1A3C6B;
      color: white;
      font-weight: bold;
    }

    tfoot td { padding: 7px 8px; font-size: 11px; }

    .text-right  { text-align: right;  }
    .text-center { text-align: center; }

    .footer {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: #1A3C6B;
      color: #B8D4F0;
      font-size: 9px;
      padding: 6px 24px;
      display: flex;
      justify-content: space-between;
    }
  </style>
</head>
<body>

  <!-- En-tête -->
  <div class="header">
    <h1>💊 Pharma Manager — Rapport des Ventes</h1>
    <p>Période : {{ \Carbon\Carbon::parse($debut)->format('d/m/Y') }}
       au {{ \Carbon\Carbon::parse($fin)->format('d/m/Y') }}</p>
  </div>

  <!-- Métriques globales -->
  <div class="meta">
    <div class="meta-item">
      <div class="val">{{ $totalVentes }}</div>
      <div class="lbl">Ventes</div>
    </div>
    <div class="meta-item">
      <div class="val">{{ number_format($totalCA, 0, ',', ' ') }} FCFA</div>
      <div class="lbl">Chiffre d'affaires</div>
    </div>
    <div class="meta-item">
      <div class="val">{{ $totalVentes > 0 ? number_format($totalCA / $totalVentes, 0, ',', ' ') : 0 }} FCFA</div>
      <div class="lbl">Panier moyen</div>
    </div>
    <div class="meta-item">
      <div class="val">{{ \Carbon\Carbon::parse($debut)->diffInDays($fin) + 1 }} jours</div>
      <div class="lbl">Durée</div>
    </div>
  </div>

  <!-- Tableau ventes par jour -->
  <div class="section-title">Détail par jour</div>
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th class="text-center">Nb ventes</th>
        <th class="text-right">CA (FCFA)</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($ventesParJour as $ligne)
      <tr>
        <td>{{ \Carbon\Carbon::parse($ligne->date)->format('d/m/Y') }}</td>
        <td class="text-center">{{ $ligne->nombre }}</td>
        <td class="text-right">{{ number_format($ligne->total, 0, ',', ' ') }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td>TOTAL</td>
        <td class="text-center">{{ $totalVentes }}</td>
        <td class="text-right">{{ number_format($totalCA, 0, ',', ' ') }} FCFA</td>
      </tr>
    </tfoot>
  </table>

  <!-- Top produits -->
  <div class="section-title">Top 10 produits vendus</div>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Médicament</th>
        <th class="text-center">Qté vendue</th>
        <th class="text-right">CA (FCFA)</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($topProduits as $i => $prod)
      <tr>
        <td class="text-center">{{ $i + 1 }}</td>
        <td>{{ $prod->medicament?->nom ?? '—' }}</td>
        <td class="text-center">{{ $prod->total_vendu }}</td>
        <td class="text-right">{{ number_format($prod->ca, 0, ',', ' ') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Pied de page -->
  <div class="footer">
    <span>Pharma Manager — Rapport généré le {{ now()->format('d/m/Y à H:i') }}</span>
    <span>Confidentiel</span>
  </div>

</body>
</html>
