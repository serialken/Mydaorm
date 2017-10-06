CREATE TABLE pai_png_ev_factoryw(
relationcontrat varchar(36) COLLATE utf8_unicode_ci NOT NULL, 
dateeffet       date NOT NULL, 
poste           varchar(10) COLLATE utf8_unicode_ci NOT NULL,
noordre         DECIMAL(2,0), 
nb              DECIMAL(11,2),
tx              DECIMAL(11,3),
mtt             DECIMAL(11,2) 
)
ENGINE = INNODB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
 
CREATE INDEX pai_png_ev_factoryw_idx1 ON pai_png_ev_factoryw (relationcontrat) ;
CREATE INDEX pai_png_ev_factoryw_idx2 ON pai_png_ev_factoryw (relationcontrat,dateeffet,poste,noordre) ;