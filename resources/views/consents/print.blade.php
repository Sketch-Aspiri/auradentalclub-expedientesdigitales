@php
    use App\Enums\ConsentType;

    $field = fn (?string $value) => filled($value) ? $value : '________________________';
    $check = fn (bool $on) => $on ? '☑' : '☐';
    $sex = $patient->sex === 'M' ? 'Masculino' : 'Femenino';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Sin nombre de paciente en el <title> (historial / screen-share): CLAUDE.md §5. --}}
    <title>Consentimiento informado</title>
    <link rel="icon" type="image/png" href="{{ asset('logos/monograma.png') }}">
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 40px 44px;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #1f1f1e; background: #fff; font-size: 12px; line-height: 1.45;
        }
        .sheet { max-width: 760px; margin: 0 auto; }
        header { text-align: center; border-bottom: 1.5px solid #3E5419; padding-bottom: 10px; }
        header img { height: 54px; width: auto; }
        h1 { font-size: 15px; letter-spacing: .03em; margin: 14px 0 2px; }
        .laws { font-size: 9.5px; color: #444; margin: 0; }
        .preamble { font-size: 10.5px; text-align: justify; margin: 12px 0 4px; }
        h2 {
            font-size: 12px; margin: 16px 0 4px; text-decoration: underline;
            text-underline-offset: 2px;
        }
        .row { margin: 3px 0; }
        .row .lbl { font-weight: 600; }
        .fill { border-bottom: 1px solid #555; display: inline-block; min-width: 160px; padding: 0 4px; }
        dl.health-print { margin: 4px 0; }
        dl.health-print > div { display: flex; gap: 8px; padding: 2px 0; align-items: baseline; }
        dl.health-print dt { flex: 0 0 55%; }
        dl.health-print dd { flex: 1; margin: 0; border-bottom: 1px dotted #999; }
        .clauses p, .declarations p { text-align: justify; margin: 6px 0; font-size: 10.5px; }
        .accept-heading { text-align: center; font-weight: 700; letter-spacing: .05em; margin: 14px 0 4px; }
        .accept-sub { text-align: center; text-decoration: underline; margin: 0 0 10px; }
        .signatures { margin-top: 34px; }
        .sig-grid { display: flex; justify-content: space-between; gap: 40px; text-align: center; margin-top: 8px; }
        .sig { flex: 1; }
        .sig .img { height: 60px; display: flex; align-items: flex-end; justify-content: center; }
        .sig img { max-height: 58px; max-width: 100%; }
        .sig .line { border-bottom: 1px solid #333; margin-top: 6px; }
        .sig .cap { font-size: 10px; margin-top: 4px; font-weight: 600; }
        .sig .name { font-size: 9.5px; color: #444; }
        .witness-title { text-align: center; font-weight: 700; margin: 26px 0 0; }
        .voided-stamp {
            color: #b91c1c; border: 3px solid #b91c1c; display: inline-block;
            padding: 4px 18px; font-weight: 800; letter-spacing: .12em; transform: rotate(-5deg);
            margin: 10px 0;
        }
        .toolbar { text-align: right; margin-bottom: 14px; }
        .toolbar button {
            font: inherit; padding: 8px 16px; border: 1px solid #3E5419; background: #3E5419;
            color: #fff; border-radius: 6px; cursor: pointer;
        }
        @media print {
            body { padding: 0; }
            .toolbar { display: none; }
            h2 { page-break-after: avoid; }
            .signatures { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="sheet">
    <div class="toolbar"><button type="button" onclick="window.print()">Imprimir</button></div>

    <header>
        <img src="{{ asset('logos/logo_completo.png') }}" alt="aura dental club">
        <h1>CONSENTIMIENTO INFORMADO</h1>
        <p class="laws">
            LEY GENERAL DE SALUD, TÍTULO QUINTO, CAPÍTULO ÚNICO, INVESTIGACIÓN PARA LA SALUD. ART. 100 FRACCIÓN IV, ART. 102, 103.<br>
            CÓDIGO CIVIL DE OBLIGACIONES EN GENERAL SOBRE EL CONSENTIMIENTO. ART. 1803 Y 1812.
        </p>
    </header>

    @if ($consent->isVoided())
        <div class="voided-stamp">ANULADO</div>
        <p style="color:#b91c1c;font-size:10px;">
            Anulado el {{ $consent->voided_at->format('d/m/Y H:i') }}@if ($consent->voidedBy) por {{ $consent->voidedBy->name }}@endif.
            Motivo: {{ $consent->void_reason }}
        </p>
    @endif

    <p class="preamble">
        En caso de tratarse de un menor de edad, o de un paciente que se encuentre en estado de
        incapacidad transitoria o permanente, o que por su situación legal no pueda expedir el
        consentimiento libre, la autorización será suscrita por el familiar más cercano en vínculo
        que lo acompañe o en su caso por su tutor o representante legal. Cuando no sea posible
        obtener la autorización por incapacidad del paciente y en ausencia de familiares o
        representante legal, los médicos autorizados, previa valoración del caso y con acuerdo de
        por lo menos dos de ellos, llevarán a cabo el procedimiento terapéutico que el caso
        requiera, dejando constancia por escrito en el expediente clínico. (Reglamento de la Ley
        General de Salud, en materia de prestación de servicios de atención médica, Capítulo IV,
        Art. 81.)
    </p>

    <h2>Datos personales</h2>
    <div class="row"><span class="lbl">Nombre del paciente:</span> <span class="fill">{{ $patient->full_name }}</span></div>
    <div class="row">
        <span class="lbl">Edad:</span> <span class="fill">{{ $patient->age }} años</span>
        &nbsp;&nbsp;<span class="lbl">Sexo:</span> <span class="fill">{{ $sex }}</span>
        &nbsp;&nbsp;<span class="lbl">Teléfono:</span> <span class="fill">{{ $patient->phone }}</span>
    </div>
    <div class="row"><span class="lbl">Domicilio:</span> <span class="fill" style="min-width:400px">{{ $patient->address ?? '—' }}</span></div>
    <div class="row">
        <span class="lbl">Persona que recibió la información y da el consentimiento:</span>
        {{ $check($consent->given_by === \App\Enums\ConsentGiver::Patient) }} Paciente
        {{ $check($consent->given_by === \App\Enums\ConsentGiver::LegalRepresentative) }} Representante legal
        {{ $check($consent->given_by === \App\Enums\ConsentGiver::FamilyMember) }} Familiar
        &nbsp;<span class="lbl">Parentesco:</span> <span class="fill">{{ $consent->relationship ?? '—' }}</span>
    </div>
    <div class="row"><span class="lbl">Cirujano dentista tratante:</span> <span class="fill">{{ $consent->doctor?->name ?? '—' }}</span></div>

    <h2>Diagnóstico</h2>
    @if (filled($consent->diagnosis))
        <div class="row">{{ $consent->diagnosis }}</div>
    @endif
    <x-consent-health-summary :data="$consent->health_snapshot ?? []" variant="print" />

    <h2>Plan de tratamiento</h2>
    <div class="row">{{ $field($consent->treatment_plan) }}</div>

    <h2>Pronóstico</h2>
    <div class="row">{{ $field($consent->prognosis) }}</div>

    <h2>Riesgos y complicaciones posibles</h2>
    <div class="row">{{ $field($consent->risks_complications) }}</div>

    <h2>Alternativas de manejo</h2>
    <div class="row">{{ $field($consent->management_alternatives) }}</div>

    <div class="row" style="margin-top:8px">
        <span class="lbl">Acepta el tratamiento:</span>
        {{ $check($consent->patient_accepts) }} Sí &nbsp; {{ $check(! $consent->patient_accepts) }} No
        &nbsp;&nbsp;·&nbsp;&nbsp;
        {{ $check($consent->authorizes_photos_xrays) }} Autoriza fotografías y radiografías con fines clínicos
    </div>

    <div class="clauses">
        <p><b>A.</b> El propósito del procedimiento propuesto consiste en diagnosticar, corregir, modificar o
        eliminar la o las alteraciones o deformidades, con el objetivo de preservar la vida, recuperar la
        función y acercarse lo más posible a la normalidad mediante su restauración, reconstrucción,
        corrección o modificación.</p>
        <p><b>B.</b> La intervención precisa de anestesia local y/o regional será evaluada e indicada por el
        cirujano dentista tratante y se proporcionará al paciente para su aceptación.</p>
        <p><b>C.</b> Es importante conocer que todo tratamiento médico-quirúrgico con fines de diagnóstico o de
        tratamiento, tanto por la propia técnica operatoria como por la situación del estado general de cada
        paciente (edad, enfermedades asociadas, diabetes, hipertensión, desnutrición, anemia, obesidad,
        hepatopatías, etc.), lleva implícita una serie de complicaciones comunes y potencialmente serias que
        podrán requerir tratamientos complementarios tanto médicos como quirúrgicos durante el transoperatorio
        y/o en el postoperatorio, los cuales por sí solos conllevan un porcentaje de morbilidad y mortalidad.</p>
        <p><b>D.</b> Se aclaró al paciente (su familiar o representante legal) que los antecedentes clínicos
        patológicos referidos en la historia clínica pueden considerarse como causas naturales de
        complicaciones potenciales para el procedimiento actual.</p>
    </div>

    <div class="declarations">
        <p style="font-weight:700;text-decoration:underline;">POR LO TANTO, CON LA INFORMACIÓN VERBAL Y ESCRITA:</p>
        <p><b>1.</b> Declaro de forma libre y voluntaria, sin existir ninguna presión física o moral sobre mí o
        mi paciente, que he comprendido las explicaciones que se me han proporcionado sobre el propósito y los
        riesgos del procedimiento, aclarando las dudas que he planteado. Asimismo, declaro haber leído y
        comprendido totalmente este consentimiento y los espacios en blanco que han sido llenados antes de firmar.</p>
        <p><b>2.</b> Estoy enterado(a) de que en cualquier momento y sin necesidad de dar explicaciones puedo
        revocar el consentimiento que ahora otorgo.</p>
    </div>

    <p class="accept-heading">ACEPTO</p>
    <p class="accept-sub">QUE SE ME (LE) REALICE EL / LOS PROCEDIMIENTO(S) PLANEADO(S)</p>

    <div class="signatures">
        <div class="sig-grid">
            <div class="sig">
                <div class="img">
                    @if ($consent->signaturePaths()['patient'])
                        <img src="{{ route('consents.signature', [$consent, 'patient']) }}" alt="Firma de consentimiento">
                    @endif
                </div>
                <div class="line"></div>
                <div class="cap">CONSENTIMIENTO</div>
                <div class="name">{{ $consent->patient->full_name }}</div>
            </div>
            <div class="sig">
                <div class="img">
                    @if ($consent->signaturePaths()['doctor'])
                        <img src="{{ route('consents.signature', [$consent, 'doctor']) }}" alt="Firma del odontólogo tratante">
                    @endif
                </div>
                <div class="line"></div>
                <div class="cap">ODONTÓLOGO TRATANTE</div>
                <div class="name">{{ $consent->doctor?->name ?? '' }}</div>
            </div>
        </div>

        <p class="witness-title">TESTIGOS</p>
        <div class="sig-grid">
            @foreach (['witness1' => $consent->witness1_name, 'witness2' => $consent->witness2_name] as $party => $wname)
                <div class="sig">
                    <div class="img">
                        @if ($consent->signaturePaths()[$party])
                            <img src="{{ route('consents.signature', [$consent, $party]) }}" alt="Firma de testigo">
                        @endif
                    </div>
                    <div class="line"></div>
                    <div class="cap">Nombre y firma</div>
                    <div class="name">{{ $wname ?? '' }}</div>
                </div>
            @endforeach
        </div>
    </div>

    @if ($consent->type === ConsentType::Extraction)
        <p style="margin-top:20px;font-size:9.5px;color:#555;">
            Consentimiento de extracción dental — referencia normativa adicional: NOM-013-SSA2-2015.
        </p>
    @endif
    <p style="margin-top:10px;font-size:9px;color:#777;">
        Documento generado el {{ now()->format('d/m/Y H:i') }} ·
        @if ($consent->signed_at) Firmado el {{ $consent->signed_at->format('d/m/Y H:i') }} @else Borrador sin firmar @endif
    </p>
</div>
</body>
</html>
