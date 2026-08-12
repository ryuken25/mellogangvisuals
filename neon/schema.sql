-- MellogangVisuals optional content layer
-- Public frontend remains static-first; this schema is for future CMS/admin/API use.

create table if not exists portfolio_projects (
  id text primary key,
  slug text unique not null,
  title text not null,
  category text not null,
  year text,
  description text not null,
  cover_url text not null,
  video_url text,
  source_url text,
  published boolean not null default true,
  sort_order integer not null default 0,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists portfolio_media (
  id bigserial primary key,
  project_id text not null references portfolio_projects(id) on delete cascade,
  media_type text not null check (media_type in ('image','video','embed')),
  url text not null,
  alt_text text,
  width integer,
  height integer,
  source_platform text check (source_platform in ('local','instagram','youtube','other')),
  sort_order integer not null default 0,
  created_at timestamptz not null default now()
);

create table if not exists booking_inquiries (
  id bigserial primary key,
  project_type text not null,
  name text not null,
  preferred_date date not null,
  location text not null,
  notes text,
  status text not null default 'new' check (status in ('new','reviewing','replied','closed')),
  created_at timestamptz not null default now()
);

create index if not exists portfolio_projects_published_sort_idx on portfolio_projects (published, sort_order);
create index if not exists portfolio_media_project_sort_idx on portfolio_media (project_id, sort_order);
create index if not exists booking_inquiries_status_created_idx on booking_inquiries (status, created_at desc);

-- Seed records mirror the verified public YouTube/local portfolio sources used by the static site.
insert into portfolio_projects (id, slug, title, category, year, description, cover_url, video_url, source_url, sort_order)
values
('indra-suci','indra-suci','Indra & Suci','Prewedding','2024','Prewedding session with an emphasis on natural movement, location and the relationship between the couple.','/assets/video/indra-suci.jpg','https://www.youtube.com/watch?v=7RwTWRgLmHY','https://www.youtube.com/@mellogangvisuals',10),
('eka-nanda','eka-nanda','Eka & Nanda','Prewedding','2024','A location-led prewedding film with a relaxed pace and a clear focus on the couple.','/assets/video/eka-nanda.jpg','https://www.youtube.com/watch?v=8kSnL2fBCTU','https://www.youtube.com/@mellogangvisuals',9),
('bukit-lestari','bukit-lestari','Puncak Bukit Lestari','Commercial / Destination','2023','Promotional film for a destination property, built around landscape, atmosphere and place.','/assets/video/bukit-lestari.jpg','https://www.youtube.com/watch?v=t4hcCZhzOdo','https://www.youtube.com/@mellogangvisuals',8),
('blooms-short','blooms-short','The Blooms Garden Bali','Destination / Commercial','2023','Short-form promotional edit for a destination in Bali.','/assets/video/blooms-short.jpg','https://www.youtube.com/watch?v=bIOMXfEdCEc','https://www.youtube.com/@mellogangvisuals',7),
('blooms-promo','blooms-promo','Blooms Garden Promo','Destination / Commercial','2023','A promotional video focused on the experience, setting and visual identity of the destination.','/assets/video/blooms-promo.jpg','https://www.youtube.com/watch?v=h6Q0_5upkk4','https://www.youtube.com/@mellogangvisuals',6),
('mandiri-taspen','mandiri-taspen','Mandiri Taspen','Corporate','2023','Corporate learning centre documentation and promotional video production.','/assets/video/mandiri-taspen.jpg','https://www.youtube.com/watch?v=Gi4BH7nQQXo','https://www.youtube.com/@mellogangvisuals',5)
on conflict (id) do update set title=excluded.title, description=excluded.description, cover_url=excluded.cover_url, video_url=excluded.video_url, source_url=excluded.source_url, updated_at=now();
