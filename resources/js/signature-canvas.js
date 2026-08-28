/**
 * Componente Alpine para los recuadros de firma del consentimiento (Sprint 5).
 *
 * Envuelve `signature_pad` sobre un <canvas> y mantiene sincronizada una propiedad de Livewire
 * con el dataURL PNG de la firma. La firma real se re-codifica y valida en el servidor
 * (App\Support\SignatureImage) — aquí solo se captura el trazo.
 *
 * Uso en Blade:
 *   <div x-data="signatureCanvas($wire, 'patientSignature')" ...>
 *     <canvas x-ref="canvas" @pointerdown/@pointerup..."></canvas>
 *     <button type="button" @click="clear()">Borrar</button>
 *   </div>
 */
import SignaturePad from 'signature_pad';

export function initSignatureCanvas() {
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('signatureCanvas', ($wire, prop) => ({
            pad: null,

            init() {
                const canvas = this.$refs.canvas;

                this.resizeCanvas(canvas);
                this.pad = new SignaturePad(canvas, {
                    penColor: '#515150', // aura-gray-dark
                    backgroundColor: 'rgba(255, 255, 255, 0)',
                });

                // Al terminar cada trazo, empuja el dataURL a Livewire sin re-render (defer).
                this.pad.addEventListener('endStroke', () => {
                    $wire.set(prop, this.pad.isEmpty() ? '' : this.pad.toDataURL('image/png'), false);
                });

                // Reajuste al cambiar el tamaño de la ventana: preserva el trazo existente.
                this._onResize = () => {
                    const data = this.pad.toData();
                    this.resizeCanvas(canvas);
                    this.pad.clear();
                    this.pad.fromData(data);
                };
                window.addEventListener('resize', this._onResize);
            },

            destroy() {
                window.removeEventListener('resize', this._onResize);
            },

            resizeCanvas(canvas) {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width * ratio;
                canvas.height = rect.height * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
            },

            clear() {
                this.pad.clear();
                $wire.set(prop, '', false);
            },
        }));
    });
}
