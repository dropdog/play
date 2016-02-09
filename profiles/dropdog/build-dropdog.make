core = 8.x
api = 2

; include the D.O. profile base
includes[core] = drupal-org-core.make

; Profile
projects[dropdog][type] = profile
projects[dropdog][download][type] = git
projects[dropdog][download][url] = git@github.com:dropdog/dropdog.git
projects[dropdog][download][branch] = develop

; Alternative branches to test
;projects[dropdog][download][branch] = master
;projects[dropdog][download][branch] = [release_tag]
