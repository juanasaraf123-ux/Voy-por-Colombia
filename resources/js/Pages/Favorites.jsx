import { Head, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Icon from '../components/Icon';
import { Card, Empty, PageHero } from '../components/Content';
import { useFavorites } from '../lib/store';
import { getJSON, u } from '../lib/url';

export default function Favorites() {
    const [ids] = useFavorites();
    const [items, setItems] = useState(null);
    useEffect(() => {
        if (!ids.length) { setItems([]); return; }
        getJSON('/api/publicaciones?ids=' + ids.join(',')).then((r) => setItems(r.items)).catch(() => setItems([]));
    }, [ids]);
    return (
        <>
            <Head title="Mis favoritos" />
            <PageHero color="var(--rosa)" icon="heart" eyebrow="Tus favoritos" title="Lugares que" accent="no quieres olvidar" compact
                lead="Se guardan en este navegador, sin necesidad de crear una cuenta. Toca el corazón en cualquier destino para agregarlo." />
            <section className="section" style={{ paddingTop: 20 }}>
                <div className="container">
                    {items === null ? <p className="muted">Cargando…</p> : items.length ? (
                        <div className="grid grid-3">{items.map((e) => <Card key={e.id} e={e} showSection />)}</div>
                    ) : (
                        <Empty icon="heart" title="Aún no tienes favoritos" text="Explora el mapa y guarda los lugares que te inspiren.">
                            <p className="flex center mt-4"><Link className="btn btn-primary" href={u('/')}><Icon name="compass" /> Explorar destinos</Link></p>
                        </Empty>
                    )}
                </div>
            </section>
        </>
    );
}
