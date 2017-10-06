rollback;
declare
  ret integer;
begin
  ret:=DBMS_HS_PASSTHROUGH.EXECUTE_IMMEDIATE@MROAD('delete from ref_activite');
  commit;
end;
/
begin
  for a in (SELECT  distinct a.codeactivite,a.libactivite,a.datedeb,a.datefin,decode(pa.affichagemodele,'O',1,0) as affichagemodele,decode(pa.affichageheure,'O',1,0) as affichageheure,decode(pa.affichagekm,'O',1,0) as affichagekm
            FROM  activite a,poste_paie_activite pa
            WHERE a.valide='O'
            and a.codeactivite=pa.codeactivite and codestatut='P'
    ) loop
    insert into "ref_activite"@MROAD("code","libelle","date_debut","date_fin","affichage_modele","affichage_duree","affichage_km") values(a.codeactivite,a.libactivite,a.datedeb,a.datefin,a.affichagemodele,a.affichageheure,a.affichagekm);
  end loop;
  commit;
end;
/
begin
  for a in (SELECT  ra."id" as id,decode(pa.codetypejour,'S',1,'D',2,'F',3) as typejour,trim(codepaieplekm) as codepaieplekm,trim(codepaiepletemps1) as codepaiepletemps1,trim(codepaiepletemps2) as codepaiepletemps2
            FROM poste_paie_activite pa,"ref_activite"@MROAD ra
            WHERE pa.codeactivite=ra."code" and pa.codestatut='P'
    ) loop
    insert into "pai_ref_postepaie_activite"@MROAD("population_id","activite_id","typejour_id","poste_km","poste_hj","poste_hn") values(1,a.id,a.typejour,a.codepaieplekm,a.codepaiepletemps1,a.codepaiepletemps2);
  end loop;
  commit;
end;
/


begin
  for a in (SELECT  codetitredcs,decode(codepaie2,'SP4',1,2) as typeurssaf_id,codepaieple2
            FROM poste_paie_titre pt, titre t 
            where pt.codetitre=t.codetitre
            and codetitredcs is not null
    ) loop
    insert into "pai_ref_postepaie_supplement"@MROAD("population_id","produit_id","typeurssaf_id","poste") values(1,a.codetitredcs,a.typeurssaf_id,a.codepaieple2);
  end loop;
  commit;
end;
/
SELECT  codetitredcs,decode(codepaie2,'SP4','C','R') as typeurssaf,codepaieple2
            FROM poste_paie_titre pt, titre t 
            where pt.codetitre=t.codetitre
            and codetitredcs is not null;
select * from poste_paie_titre pt, titre t where pt.codetitre=t.codetitre;
commit;
            rollback