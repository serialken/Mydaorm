delete ppt 
from pai_prd_tournee ppt
inner join pai_tournee pt on ppt.tournee_id=pt.id
where pt.date_distrib<'2014-10-01';

delete pj
from pai_journal pj
inner join pai_tournee pt on pj.tournee_id=pt.id
where pt.date_distrib<'2014-10-01';

delete pr
from pai_reclamation pr
inner join pai_tournee pt on pr.tournee_id=pt.id
where pt.date_distrib<'2014-10-01';

update pai_tournee pt
set tournee_org_id=null
where pt.date_distrib<'2014-10-01';

update client_a_servir_logist
set pai_tournee_id=null
where date_distrib<'2014-10-01';

delete pt 
from pai_tournee pt
where pt.date_distrib<'2014-10-01';

delete pj
from pai_journal pj
inner join pai_activite pa on pj.activite_id=pa.id
where pa.date_distrib<'2014-10-01';

delete pa
from pai_activite pa
where pa.date_distrib<'2014-10-01';

delete ph
from pai_heure ph
where ph.date_distrib<'2014-10-01';

commit;