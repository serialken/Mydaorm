-- pb sur tournee splittee
select * from pai_tournee
where tournee_org_id is null
and id in (select tournee_org_id from pai_tournee pt2 where split_id is not null)
