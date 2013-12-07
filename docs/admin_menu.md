On utilise les options de routing pour créer des entrées. 

```yaml
chill_appointment_admin:
     pattern: /admin
     defaults: { _controller: CLChillAppointmentBundle:Admin:index }
     options:
         menu: admin
         label: menu.Appointment.index.title
         helper: menu.Appointment.index.helper
         order: 200
```

Attention à ne pas utiliser deux fois le même `order`, sinon l'entrée est supprimée...

Et puis, il "suffit" de créer la fonction correspondante dans un controller.

On peut se contenter d'un "forward" vers la fonction index du controller admin dans le bundle main ('CLChillMainBundle:Controller:index') :

```php
    public function indexAction() {
         return $this->forward('CLChillMainBundle:Admin:index',
                 array(
                     'menu' => 'admin_appointment',
                     'page_title' => 'menu.appointment.admin.index',
                     'header_title' => 'menu.appointment.header_index'
                     )
                 );
     }
```

qui ira chercher à son tour toutes les entrées ont comme option le menu correspondant. Exemple pour admin_appointment:

```yaml
options:
        menu: admin_appointment
        #(...)  
```