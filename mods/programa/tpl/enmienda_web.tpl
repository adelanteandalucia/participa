<div class="container">

    <div class="enmienda">

        <div class="row">
            <h2>Detalles enmienda</h2>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Columna</th>
                            <th scope="col">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">ID</th>
                            <td>{$e->id}</td>
                        </tr>  
                        {if ($e->idCategoria->existe)}
                            <tr>
                                <th scope="row">Categoría</th>
                                <td><a href="{$url}categoria/{$e->idCategoria->id}/" target="_blank">Ver</a> - {$e->idCategoria->nombre}</td>
                            </tr>                    
                        {/if}                        
                        {if ($e->idPropuesta->existe)}
                            <tr>
                                <th scope="row">Propuesta</th>                                
                                <td><a href="{$url}propuesta/{$e->idPropuesta->id}/" target="_blank">Ver</a> - {$e->idPropuesta->texto}</td>
                            </tr>
                        {/if}

                        {if (isset($e->tipo))}
                            <tr>
                                <th scope="row">Tipo</th>
                                <td>
                                    {if $e->tipo eq 'mod'}
                                        <span class="badge badge-warning">
                                            Modificación 
                                        </span>
                                    {/if} 
                                    {if $e->tipo eq 'add'}
                                        <span class="badge badge-success">
                                            Adición 
                                        </span>
                                    {/if}         
                                    {if $e->tipo eq 'sup'}
                                        <span class="badge badge-danger">
                                            Supresión 
                                        </span>
                                    {/if}
                                    {if $e->tipo eq 'trans'}
                                        <span class="badge badge-primary">
                                            Transacción 
                                        </span>
                                    {/if}                                    


                                </td>
                            </tr>                    
                        {/if}     
                        
                        {if (isset($e->originales))}
                            <tr>
                                <th scope="row">Procede de:</th>
                                <td>
                                    {foreach $e->originales as $enmiendaoriginal}
                                        <a href="/enmienda/{$enmiendaoriginal->id}/"><span class="badge badge-secondary">Enmienda {$enmiendaoriginal->id}</span></a>
                                    {/foreach}
                                </td>
                            </tr>                    
                        {/if}       
                        
                        {if ($e->transaccionada)}
                            <tr>
                                <th scope="row">Ha sido transaccionada en:</th>
                                <td>
                                    {foreach $e->transaccionada as $enmiendatrans}
                                        <a href="/enmienda/{$enmiendatrans}/"><span class="badge badge-primary">Enmienda {$enmiendatrans}</span></a>
                                    {/foreach}
                                </td>
                            </tr>                    
                        {/if}                            
                        
                        {if (isset($e->andaluz))}
                            <tr>
                                <th scope="row">Bloque - Patio Andaluz</th>
                                <td>{$e->andaluz}</td>
                            </tr>                    
                        {/if}                           
                        
                        {if (isset($e->cp->cp))}
                            <tr>
                                <th scope="row">Código postal</th>
                                <td>{$e->cp->municipio}</td>
                            </tr>                    
                        {/if}                      
                    </tbody>
                </table>

                <hr>
                <h3><i class="fas fa-align-left"></i> <strong>Motivación</strong></h3>
                {$e->motivacion}

                <hr>
                <h3><i class="fas fa-align-left"></i> <strong>Texto</strong></h3>
                {$e->redaccion}            

                <hr>
                <h3><i class="fas fa-paperclip"></i> <strong>Adjuntos</strong></h3>
                {if ($fichero)}
                    <a href="/fichero/{$fichero->id}/download/">{$fichero->nombre}</a>
                {else}
                    No hay ficheros adjuntos
                {/if}


                <hr>

                <h3><strong><i class="fas fa-eye"></i> Visible</strong></h3>                
                {if $e->publica}SÍ{else}NO{/if}
                <hr>

                {if (isset($smarty.session.ponencia) && ($smarty.session.ponencia eq 1))}
                <h3><strong><i class="fas fa-comments"></i> Valoraciones</strong></h3>
                <i>Ordenadas de más antigua a más reciente. La última linea es la más actual</i>
                <table class="table tabla-valoraciones">
                    <thead>
                        <tr>
                            <th scope="col">¿Quien hace la valoración?</th>
                            <th scope="col">Sentido / Valoración</th>
                            <th scope="col">Observaciones</th>
                            <th scope="col">Fecha y hora</th>
                        </tr>                        
                    </thead>
                    <tbody>
                        {foreach $valoraciones as $valoracion}
                            <tr>
                                <td>{$valoracion->valorador}</td>
                                <td>{$valoracion->valoracion}</td>
                                <td>{$valoracion->observaciones}</td>
                                <td>{$valoracion->created_at}</td>
                            </tr>
                        {/foreach}                                    

                    </tbody>
                </table>
                {/if}



            </div>        
        </div>


    </div>
</div>
<hr>
<span>
    {if (isset($smarty.session.ponencia) && ($smarty.session.ponencia eq 1))}
        Identificado como ponencia: <a href="/salir/">Salir</a>
    {/if}
</span>