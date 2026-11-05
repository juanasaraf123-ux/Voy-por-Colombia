import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import Icon from '../components/Icon';
import { Card, Empty, Wave } from '../components/Content';
import { u } from '../lib/url';

export default function Search({ q, groups, places }) {
    const [value, setValue] = useState(q);
    const total = groups.reduce((n, g) => n + g.items.length, 0) + places.length;
    return (
        <>
            <Head title={q ? `Resultados para «${q}»` : 'Buscar'} />
            <section className="page-hero" style={{ '--c': 'var(--naranja)', paddingBottom: 90 }}>
                <div className="container">
                    <span className="eyebrow"><Icon name="search" /> Buscador</span>
                    <h1>{q ? <>Resultados para <em>«{q}»</em></> : <>Encuentra <em>tu próximo viaje</em></>}</h1>
                    <form className="filters" style={{ position: 'static', margin: '24px 0 0', maxWidth: 720 }} role="search" onSubmit={(e) => { e.preventDefault(); router.get(u('/buscar'), { q: value.trim() }); }}>
                        <div className="filters-row">
                            <div className="field grow"><Icon name="search" /><label className="sr-only" htmlFor="sq">Buscar</label><input className="input" style={{ width: '100%' }} id="sq" type="search" value={value} onChange={(e) => setValue(e.target.value)} placeholder="Destinos, municipios, platos, festivales, noticias…" /></div>
                            <button className="btn btn-primary" type="submit">Buscar</button>
                        </div>
                    </form>
                </div>
                <Wave />
            </section>
            <section className="section" style={{ paddingTop: 20 }}>
                <div className="container">
                    {q && q.length < 2 && <Empty icon="search" title="Escribe al menos dos letras" />}
                    {q.length >= 2 && !total && <Empty icon="search" title="Sin resultados" text="Prueba con otra palabra, el nombre de un departamento o municipio." />}
                    {places.length > 0 && (
                        <div className="section-block">
                            <div className="section-block-head" style={{ '--c': 'var(--verde)' }}><span className="ico"><Icon name="map" /></span><h2 className="title-md" style={{ margin: 0 }}>Departamentos y municipios</h2></div>
                            <div className="chips">
                                {places.map((p) => p.type === 'department'
                                    ? <Link key={'d' + p.id} className="chip" href={u('/?departamento=' + p.slug)}><Icon name="map" /> {p.name}</Link>
                                    : <Link key={'m' + p.id} className="chip" href={u(`/?departamento=${p.departmentSlug}&municipio=${p.slug}`)}><Icon name="pin" /> {p.name} <span className="chip-count">{p.departmentName}</span></Link>)}
                            </div>
                        </div>
                    )}
                    {groups.map((g) => (
                        <div key={g.key} className="section-block" style={{ '--c': g.color }}>
                            <div className="section-block-head"><span className="ico"><Icon name={g.icon} /></span><h2 className="title-md" style={{ margin: 0 }}>{g.label} <span className="muted" style={{ fontSize: '.7em' }}>({g.items.length})</span></h2></div>
                            <div className="grid grid-4">{g.items.map((e) => <Card key={e.id} e={e} />)}</div>
                        </div>
                    ))}
                </div>
            </section>
        </>
    );
}
