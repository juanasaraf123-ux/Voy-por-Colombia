import { createContext, useCallback, useContext, useEffect, useRef, useState } from 'react';
import Icon from './Icon';
import { getJSON } from '../lib/url';
import { toast } from './Toasts';

const Ctx = createContext({ open() {}, openEntry() {} });
export const useViewer = () => useContext(Ctx);

/**
 * Visor multimedia (fotos, video, video en línea y audio) que se abre encima de la página,
 * sin navegar ni mover el contenido de fondo.
 */
export function ViewerProvider({ children }) {
    const [state, setState] = useState(null); // { items, i }
    const closeBtn = useRef(null);
    const prevFocus = useRef(null);

    const open = useCallback((items, start = 0) => {
        if (!items?.length) return;
        prevFocus.current = document.activeElement;
        setState({ items, i: start });
    }, []);

    /** Abre la multimedia de una publicación: kind = image | video | audio. */
    const openEntry = useCallback(async (id, kind = 'image') => {
        try {
            const data = await getJSON(`/api/publicaciones/${id}/multimedia`);
            const list = kind === 'video' ? data.videos : kind === 'audio' ? data.audios : data.images;
            if (!list?.length) {
                toast(kind === 'video' ? 'Este lugar aún no tiene videos' : kind === 'audio' ? 'Este lugar aún no tiene audios' : 'Este lugar aún no tiene fotos', 'info');
                return;
            }
            open(list, 0);
        } catch {
            toast('No fue posible cargar la multimedia', 'alert');
        }
    }, [open]);

    const close = useCallback(() => {
        setState(null);
        prevFocus.current?.focus?.({ preventScroll: true });
    }, []);
    const go = useCallback((d) => setState((s) => (s ? { ...s, i: (s.i + d + s.items.length) % s.items.length } : s)), []);

    useEffect(() => {
        if (!state) return undefined;
        closeBtn.current?.focus({ preventScroll: true });
        const key = (e) => {
            if (e.key === 'Escape') { e.stopPropagation(); close(); }
            if (e.key === 'ArrowLeft') go(-1);
            if (e.key === 'ArrowRight') go(1);
        };
        window.addEventListener('keydown', key, true);
        return () => window.removeEventListener('keydown', key, true);
    }, [state, close, go]);

    const it = state?.items[state.i];
    const touch = useRef(null);

    return (
        <Ctx.Provider value={{ open, openEntry }}>
            {children}
            {state && (
                <div
                    className="lightbox is-open" role="dialog" aria-modal="true" aria-label="Visor multimedia"
                    onClick={(e) => { if (e.target === e.currentTarget || e.target.classList.contains('lightbox-stage')) close(); }}
                    onTouchStart={(e) => { touch.current = e.touches[0].clientX; }}
                    onTouchEnd={(e) => { if (touch.current === null) return; const dx = e.changedTouches[0].clientX - touch.current; if (Math.abs(dx) > 50) go(dx < 0 ? 1 : -1); touch.current = null; }}
                >
                    <span className="lb-count">{state.items.length > 1 ? `${state.i + 1} / ${state.items.length}` : ''}</span>
                    <button ref={closeBtn} className="lb-btn lb-close" type="button" onClick={close} aria-label="Cerrar visor"><Icon name="close" /></button>
                    {state.items.length > 1 && <>
                        <button className="lb-btn lb-prev" type="button" onClick={() => go(-1)} aria-label="Anterior"><Icon name="chevron-left" /></button>
                        <button className="lb-btn lb-next" type="button" onClick={() => go(1)} aria-label="Siguiente"><Icon name="chevron-right" /></button>
                    </>}
                    <div className="lightbox-stage">
                        {it.kind === 'audio' && it.src ? (
                            <div className="audio-stage"><Icon name="volume" className="icon audio-ico" /><audio key={it.src} src={it.src} controls autoPlay /></div>
                        ) : it.kind === 'embed' && it.embed ? (
                            <div className="video-frame"><iframe key={it.embed} src={it.embed} title={it.title || 'Video'} allow="autoplay; encrypted-media; picture-in-picture" allowFullScreen /></div>
                        ) : it.kind === 'video' && it.src ? (
                            <div className="video-frame"><video key={it.src} src={it.src} controls autoPlay playsInline /></div>
                        ) : (
                            <img key={it.large} src={it.large || it.medium || it.thumb} alt={it.alt || it.title || ''} />
                        )}
                    </div>
                    <div className="lightbox-caption">
                        {it.title && <strong>{it.title}</strong>}
                        {it.caption && <p>{it.caption}</p>}
                        {it.credit && <small>{it.credit}{it.license ? ' · ' + it.license : ''}</small>}
                    </div>
                </div>
            )}
        </Ctx.Provider>
    );
}
