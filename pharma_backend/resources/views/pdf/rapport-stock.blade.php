<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }

    .header {
      background-color: #1A3C6B;
      color: white;
      padding: 16px 24px;
      margin-bottom: 16px;
    }
    .header h1 { font-size: 18px; font-weight: bold; }
    .header p  { font-size: 10px; color: #B8D4F0; margin-top: 3px; }

    .alertes {
      display: flex;
      gap: 12px;
      margin: 0 24px 16px 24px;
    }

    .alerte-box {
      flex: 1;
      padding: 10px;
      border-radius: 4px;
      text-align: center;
    }

    .alerte-box.rouge   { background: #FEE2E2; border-left: 4px solid #DC2626; }
    .alerte-box.orange  { background: #FEF3C7; border-left: 4px solid #D97706; }
    .alerte-box.violet  { background: #EDE9FE; border-left: 4px solid #7C3AED; }

    .alerte-box .val { font-size: 22px; font-weight: bold; }
    .alerte-box .lbl { font-size: 9px; color: #555; }

    table {
      width: calc(100% - 48px);
      margin: 0 24px;
      border-collapse: collapse;
    }

    thead tr { background-color: #1A3C6B; color: white; }
    thead th  { padding: 6px 7px; font-size: 9px; text-align: left; }

    tbody tr:nth-child(even) { background: #EBF3FB; }
    tbody tr.rupture         { background: #FEE2E2 !important; }
    tbody tr.faible          { background: #FEF9C3 !important; }
    tbody tr.expire          { background: #FCE7F3 !important; }

    tbody td { padding: 5px 7px; border-bottom: 1px solid #E5E7EB; }

    .badge {
      padding: 2px 6px;
      border-radius: 10px;
      font-size: 8px;
      font-weight: bold;
    }
    .badge.ok      { background: #D1FAE5; color: #065F46; }
    .badge.faible  { background: #FEF3C7; color: #92400E; }
    .badge.rupture { background: #FEE2E2; color: #991B1B; }
    .badge.expire  { background: #FCE7F3; color: #9D174D; }

    .text-right  { text-align: right; }
    .text-center { text-align: center; }

    .footer {
      position: fixed; bottom: 0; left: 0; right: 0;
      background: #1A3C6B; color: #B8D4F0;
      font-size: 8px; padding: 5px 24px;
      display: flex; justify-content: space-between;
    }
  </style>
</head>
<body>

  <div class="header">
    <h1>💊 Pharma Manager — Rapport de Stock</h1>
    <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
  </div>

  <!-- Alertes résumé -->
  <div class="alertes">
    <div class="alerte-box rouge">
      <div class="val">{{ $nbRupture }}</div>
      <div class="lbl">En rupture</div>
    </div>
    <div class="alerte-box orange">
      <div class="val">{{ $nbFaible }}</div>
      <div class="lbl">Stock faible</div>
    </div>
    <div class="alerte-box violet">
      <div class="val">{{ $nbExpires }}</div>
      <div class="lbl">Expirés / bientôt</div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Médicament</th>
        <th>Catégorie</th>
        <th class="text-center">Stock actuel</th>
        <th class="text-center">Stock min.</th>
        <th class="text-center">Statut</th>
        <th class="text-right">Prix vente</th>
        <th>Expiration</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($medicaments as $med)
        @php
          $isRupture = $med->stock_actuel === 0;
          $isFaible  = !$isRupture && $med->stock_actuel <= $med->stock_minimum;
          $isExpire  = $med->date_expiration && $med->date_expiration < now();
          $rowClass  = $isRupture ? 'rupture' : ($isFaible ? 'faible' : ($isExpire ? 'expire' : ''));

          if ($isRupture)       $badgeClass = 'rupture'; $badgeLabel = 'RUPTURE';
          elseif ($isFaible)  { $badgeClass = 'faible';  $badgeLabel = 'FAIBLE'; }
          else                { $badgeClass = 'ok';      $badgeLabel = 'OK'; }
        @endphp
        <tr class="{{ $rowClass }}">
          <td><strong>{{ $med->nom }}</strong>
              @if($med->dosage) <br><span style="color:#888">{{ $med->dosage }}</span>@endif
          </td>
          <td>{{ $med->categorie?->nom ?? '—' }}</td>
          <td class="text-center"><strong>{{ $med->stock_actuel }}</strong></td>
          <td class="text-center">{{ $med->stock_minimum }}</td>
          <td class="text-center">
            <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
          </td>
          <td class="text-right">{{ number_format($med->prix_vente, 0, ',', ' ') }}</td>
          <td>
            @if($med->date_expiration)
              @if($med->date_expiration < now())
                <span style="color:#DC2626;font-weight:bold">
                  EXP {{ $med->date_expiration->format('d/m/Y') }}
                </span>
              @elseif($med->date_expiration < now()->addDays(30))
                <span style="color:#D97706">
                  {{ $med->date_expiration->format('d/m/Y') }}
                </span>
              @else
                {{ $med->date_expiration->format('d/m/Y') }}
              @endif
            @else
              —
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">
    <span>Pharma Manager — Rapport stock</span>
    <span>{{ $medicaments->count() }} médicament(s) actif(s)</span>
  </div>

</body>
</html>
