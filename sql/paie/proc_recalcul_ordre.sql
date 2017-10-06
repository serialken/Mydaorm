/**
    call recalcul_ordre(null, null, null);
    call recalcul_tournee_date_distrib('2016-02-21', null, null);
    call recalcul_ordre('2016-02-21', null, null, null);
    call recalcul_ordre('2016-03-02' , 18, 1, null);
    
    
      set @rownum=0;
  set @employe_id=0;
  select
    @rownum:=if(@employe_id=pt.employe_id,@rownum+1,1),
    if (@rownum<>pt.ordre,'!!!',''),
    @employe_id:=pt.employe_id,
    pt.ordre,
    pt.* 
  from pai_tournee pt 
  where pt.date_extrait is null
  and pt.date_distrib='2016-03-11'
  and (pt.tournee_org_id is null or pt.split_id is not null)
  order by pt.employe_id,pt.code;

*/
-- ------------------------------------------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS recalcul_ordre;
CREATE PROCEDURE recalcul_ordre(
    IN 		_date_distrib   DATE,
    IN 		_depot_id       INT,
    IN 		_flux_id        INT,
    IN 		_employe_id     INT
) BEGIN
  set @date_distrib='2000-01-01';
  set @rownum=0;
  set @employe_id=0;
  update pai_tournee pt
  inner join (select
                pt.id,
                @rownum:=if(@date_distrib=pt.date_distrib and @employe_id=pt.employe_id,@rownum+1,1) as ordre,
                @employe_id:=pt.employe_id,
                @date_distrib:=pt.date_distrib
              from pai_tournee pt 
              where pt.date_extrait is null
              and (pt.date_distrib=_date_distrib or _date_distrib is null)
              and (pt.depot_id=_depot_id or _depot_id is null)
              and (pt.flux_id=_flux_id or _flux_id is null)
              and (pt.employe_id=_employe_id or _employe_id is null)
              and (pt.tournee_org_id is null or pt.split_id is not null)
              order by pt.date_distrib,pt.employe_id,pt.code
              ) as ptn on pt.id=ptn.id
  set pt.ordre=ptn.ordre
  where pt.ordre<>ptn.ordre
  and pt.date_extrait is null
  and (pt.date_distrib=_date_distrib or _date_distrib is null)
  and (pt.depot_id=_depot_id or _depot_id is null)
  and (pt.flux_id=_flux_id or _flux_id is null)
  and (pt.employe_id=_employe_id or _employe_id is null)
  and (pt.tournee_org_id is null or pt.split_id is not null)
  ;
END;