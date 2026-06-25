@extends('layouts.app')
@section('title', 'Import CSV')
@section('page-title', 'Import CSV')
@section('page-subtitle', 'Importez des élèves ou enseignants depuis un fichier CSV')

@section('content')

@if(session('success'))
<div style="background:#D1FAE5;border:1.5px solid #6EE7B7;color:#065F46;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;gap:0.75rem;align-items:flex-start">
    <i class="bi bi-check-circle-fill" style="font-size:1.1rem;flex-shrink:0;margin-top:1px"></i>
    <div>
        <div style="font-weight:600;font-size:0.875rem">{{ session('success') }}</div>
        @if(session('erreurs_import') && count(session('erreurs_import')) > 0)
        <div style="margin-top:0.5rem;font-size:0.8rem">
            Lignes ignorées :
            @foreach(session('erreurs_import') as $err)
            <div style="color:#065F46;opacity:0.8">• {{ $err }}</div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">

    {{-- Télécharger les modèles --}}
    <div class="card-section">
        <div class="card-header-row"><h2><i class="bi bi-download me-2" style="color:#4F46E5"></i>Modèles CSV</h2></div>
        <div style="padding:1.25rem">
            <p style="font-size:0.875rem;color:#6B7280;margin-bottom:1.25rem">
                Téléchargez un modèle pour préparer votre fichier d'import. Compatible Excel et LibreOffice.
            </p>
            <div style="display:flex;flex-direction:column;gap:0.75rem">
                <a href="{{ route('admin.import-csv.modele', 'eleves') }}"
                   style="display:flex;align-items:center;gap:0.875rem;padding:0.875rem;background:#F9FAFB;border:1.5px solid #E5E7EB;border-radius:10px;text-decoration:none;color:#374151;transition:all 0.15s"
                   onmouseover="this.style.borderColor='#4F46E5';this.style.background='#EEF2FF'"
                   onmouseout="this.style.borderColor='#E5E7EB';this.style.background='#F9FAFB'">
                    <div style="width:40px;height:40px;background:#EEF2FF;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#4F46E5;font-size:1.1rem;flex-shrink:0">
                        <i class="bi bi-file-earmark-person"></i>
                    </div>
                    <div>
                        <div style="font-size:0.875rem;font-weight:600">Modèle élèves</div>
                        <div style="font-size:0.75rem;color:#6B7280">nom, prénoms, email, matricule, téléphone</div>
                    </div>
                    <i class="bi bi-download" style="margin-left:auto;color:#4F46E5"></i>
                </a>

                <a href="{{ route('admin.import-csv.modele', 'enseignants') }}"
                   style="display:flex;align-items:center;gap:0.875rem;padding:0.875rem;background:#F9FAFB;border:1.5px solid #E5E7EB;border-radius:10px;text-decoration:none;color:#374151;transition:all 0.15s"
                   onmouseover="this.style.borderColor='#059669';this.style.background='#ECFDF5'"
                   onmouseout="this.style.borderColor='#E5E7EB';this.style.background='#F9FAFB'">
                    <div style="width:40px;height:40px;background:#D1FAE5;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#059669;font-size:1.1rem;flex-shrink:0">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <div style="font-size:0.875rem;font-weight:600">Modèle enseignants</div>
                        <div style="font-size:0.75rem;color:#6B7280">nom, prénoms, email, téléphone</div>
                    </div>
                    <i class="bi bi-download" style="margin-left:auto;color:#059669"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Conseils --}}
    <div class="card-section">
        <div class="card-header-row"><h2><i class="bi bi-info-circle me-2" style="color:#D97706"></i>Conseils</h2></div>
        <div style="padding:1.25rem">
            <ul style="font-size:0.875rem;color:#374151;line-height:2;padding-left:1.25rem">
                <li>Utilisez le <strong>modèle fourni</strong> pour éviter les erreurs</li>
                <li>L'<strong>email</strong> doit être unique pour chaque utilisateur</li>
                <li>Le <strong>matricule</strong> est optionnel</li>
                <li>Le mot de passe par défaut sera <code style="background:#F3F4F6;padding:1px 6px;border-radius:4px">password123</code></li>
                <li>Les élèves peuvent être affectés directement à une classe</li>
                <li>Encodage recommandé : <strong>UTF-8</strong></li>
                <li>Séparateur recommandé : <strong>point-virgule</strong> (pour Excel)</li>
            </ul>
        </div>
    </div>
</div>

{{-- Formulaire d'import --}}
<div class="card-section">
    <div class="card-header-row">
        <h2><i class="bi bi-upload me-2" style="color:#4F46E5"></i>Importer un fichier CSV</h2>
    </div>
    <div style="padding:1.5rem">
        <form method="POST" action="{{ route('admin.import-csv.previsualiser') }}" enctype="multipart/form-data"
              id="formImport">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem">

                {{-- Type --}}
                <div>
                    <label style="font-size:0.875rem;font-weight:600;display:block;margin-bottom:0.5rem">
                        Type d'utilisateurs <span style="color:#DC2626">*</span>
                    </label>
                    <div style="display:flex;gap:0.75rem">
                        <label style="flex:1;cursor:pointer">
                            <input type="radio" name="type" value="eleves" checked style="display:none" id="typeEleves">
                            <div id="labelEleves"
                                 style="padding:0.875rem;border:2px solid #4F46E5;background:#EEF2FF;border-radius:10px;text-align:center;transition:all 0.15s">
                                <i class="bi bi-people-fill" style="color:#4F46E5;font-size:1.25rem;display:block;margin-bottom:0.3rem"></i>
                                <span style="font-size:0.875rem;font-weight:600;color:#4F46E5">Élèves</span>
                            </div>
                        </label>
                        <label style="flex:1;cursor:pointer">
                            <input type="radio" name="type" value="enseignants" style="display:none" id="typeEnseignants">
                            <div id="labelEnseignants"
                                 style="padding:0.875rem;border:2px solid #E5E7EB;background:#F9FAFB;border-radius:10px;text-align:center;transition:all 0.15s">
                                <i class="bi bi-person-badge" style="color:#6B7280;font-size:1.25rem;display:block;margin-bottom:0.3rem"></i>
                                <span style="font-size:0.875rem;font-weight:600;color:#6B7280">Enseignants</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Séparateur --}}
                <div>
                    <label style="font-size:0.875rem;font-weight:600;display:block;margin-bottom:0.5rem">
                        Séparateur CSV
                    </label>
                    <select name="separateur"
                            style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem">
                        <option value="point-virgule" selected>Point-virgule ; (recommandé pour Excel)</option>
                        <option value="virgule">Virgule ,</option>
                        <option value="tabulation">Tabulation</option>
                    </select>
                </div>
            </div>

            {{-- Classe (pour élèves) --}}
            <div id="zoneClasse" style="margin-bottom:1.25rem">
                <label style="font-size:0.875rem;font-weight:600;display:block;margin-bottom:0.5rem">
                    Affecter à la classe <span style="color:#6B7280;font-weight:400">(optionnel)</span>
                </label>
                <select name="classe_id"
                        style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem">
                    <option value="">Ne pas affecter à une classe</option>
                    @foreach($classes as $c)
                    <option value="{{ $c->id }}">{{ $c->nom }} ({{ ucfirst($c->niveau) }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Upload fichier --}}
            <div style="margin-bottom:1.5rem">
                <label style="font-size:0.875rem;font-weight:600;display:block;margin-bottom:0.5rem">
                    Fichier CSV <span style="color:#DC2626">*</span>
                </label>
                <div id="dropzone"
                     style="border:2px dashed #E5E7EB;border-radius:12px;padding:2rem;text-align:center;cursor:pointer;transition:all 0.2s;background:#F9FAFB"
                     onclick="document.getElementById('fichierInput').click()"
                     ondragover="event.preventDefault();this.style.borderColor='#4F46E5';this.style.background='#EEF2FF'"
                     ondragleave="this.style.borderColor='#E5E7EB';this.style.background='#F9FAFB'"
                     ondrop="handleDrop(event)">
                    <i class="bi bi-cloud-upload" style="font-size:2rem;color:#9CA3AF;display:block;margin-bottom:0.75rem"></i>
                    <div style="font-size:0.9rem;font-weight:500;color:#374151" id="dropzoneText">
                        Glissez votre fichier CSV ici ou cliquez pour sélectionner
                    </div>
                    <div style="font-size:0.78rem;color:#6B7280;margin-top:0.3rem">CSV · Max 2 Mo</div>
                    <input type="file" name="fichier" id="fichierInput" accept=".csv,.txt"
                           style="display:none" onchange="afficherNomFichier(this)">
                </div>
            </div>

            <button type="submit"
                    style="padding:0.875rem 2rem;background:#4F46E5;color:white;border:none;border-radius:12px;font-size:0.95rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:0.5rem">
                <i class="bi bi-eye"></i> Prévisualiser avant import
            </button>
        </form>
    </div>
</div>

<script>
// Toggle type élèves/enseignants
document.querySelectorAll('input[name="type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isEleve = this.value === 'eleves';

        document.getElementById('labelEleves').style.cssText =
            isEleve ? 'padding:0.875rem;border:2px solid #4F46E5;background:#EEF2FF;border-radius:10px;text-align:center'
                    : 'padding:0.875rem;border:2px solid #E5E7EB;background:#F9FAFB;border-radius:10px;text-align:center';
        document.getElementById('labelEleves').querySelector('i').style.color   = isEleve ? '#4F46E5' : '#6B7280';
        document.getElementById('labelEleves').querySelector('span').style.color = isEleve ? '#4F46E5' : '#6B7280';

        document.getElementById('labelEnseignants').style.cssText =
            !isEleve ? 'padding:0.875rem;border:2px solid #059669;background:#ECFDF5;border-radius:10px;text-align:center'
                     : 'padding:0.875rem;border:2px solid #E5E7EB;background:#F9FAFB;border-radius:10px;text-align:center';
        document.getElementById('labelEnseignants').querySelector('i').style.color   = !isEleve ? '#059669' : '#6B7280';
        document.getElementById('labelEnseignants').querySelector('span').style.color = !isEleve ? '#059669' : '#6B7280';

        document.getElementById('zoneClasse').style.display = isEleve ? 'block' : 'none';
    });
});

function afficherNomFichier(input) {
    if (input.files.length > 0) {
        document.getElementById('dropzoneText').textContent = '✓ ' + input.files[0].name;
        document.getElementById('dropzone').style.borderColor = '#4F46E5';
        document.getElementById('dropzone').style.background  = '#EEF2FF';
    }
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropzone').style.borderColor = '#E5E7EB';
    document.getElementById('dropzone').style.background  = '#F9FAFB';
    const file = e.dataTransfer.files[0];
    if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('fichierInput').files = dt.files;
        afficherNomFichier(document.getElementById('fichierInput'));
    }
}
</script>

@endsection