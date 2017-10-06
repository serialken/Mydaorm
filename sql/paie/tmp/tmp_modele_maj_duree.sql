update modele_tournee_jour mtj
    inner join modele_tournee mt on mtj.tournee_id=mt.id
    inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=23
    set nbcli_geo=(select avg(nbcli) from pai_tournee pt where pt.modele_tournee_jour_id=mtj.id)
  where nbcli_geo is null

update modele_tournee_jour mtj
    inner join modele_tournee mt on mtj.tournee_id=mt.id
    inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=23
    set nbcli_geo=nbcli_geo-1
update modele_tournee_jour mtj
    inner join modele_tournee mt on mtj.tournee_id=mt.id
    inner join groupe_tournee gt on mt.groupe_id=gt.id and gt.depot_id=23
    set nbcli_geo=nbcli_geo+1
