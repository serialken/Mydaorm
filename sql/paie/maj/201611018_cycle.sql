update emp_pop_depot 
set cycle=cycle_to_string(lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche)
where cycle='';
update emp_cycle
set cycle=cycle_to_string(lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche)
where cycle='';
