<?php
class Cerb5BlogEventAssignmentListener extends DevblocksEventListenerExtension {
	function __construct($manifest) {
		parent::__construct($manifest);
	}

	/**
	 * @param Model_DevblocksEvent $event
	 */
	function handleEvent(Model_DevblocksEvent $event) {
        $from_context = $event->params['from_context'];
 		
		switch($event->id) {
			case 'context_link.set':
                if(CerberusContexts::CONTEXT_WORKER == $event->params['to_context']) {
                    $worker_id = $event->params['to_context_id'];
                    switch($from_context) {
                        case CerberusContexts::CONTEXT_TICKET:
                            $ticket_id = $event->params['from_context_id'];
                            $ticket = DAO_Ticket::get($ticket_id);
                       		// Events
                            //Event_MailReceivedByGroup::trigger($message_id, $group->id);

                            // Trigger Worker Watchers
                            $context_watchers = CerberusContexts::getWatchers(CerberusContexts::CONTEXT_TICKET, $ticket_id);
                            if(is_array($context_watchers) && !empty($context_watchers))
                                foreach($context_watchers as $watcher_id => $watcher) {
                                    if ($watcher_id == $worker_id) {
                                        Event_Cerb5BlogTicketWatchersAssigned::trigger($ticket->first_message_id, $worker_id);
                                        Event_Cerb5BlogTicketWatchersAssignedGroup::trigger($ticket->first_message_id, $worker_id, $ticket->team_id);
                                    }
                                }
                            break;
                        /* Disabling until I have more time to work on this    
                        case CerberusContexts::CONTEXT_TASK:
                            $task_id = $event->params['from_context_id'];
                            $context_watchers = CerberusContexts::getWatchers(CerberusContexts::CONTEXT_TASK, $task_id);
                            if(is_array($context_watchers) && !empty($context_watchers))
                                foreach($context_watchers as $watcher_id => $watcher) {
                                    Event_WatchersAssignedToWorker::trigger($task_id, $watcher_id);
                                }
                            break;
                        */
                        
                    }	
                }
   			case 'dao.ticket.update':
                $objects = $event->params['objects'];
               	if(is_array($objects))
                    foreach($objects as $object_id => $object) {
                        @$model = $object['model'];
                        @$changes = $object['changes'];

                        if(empty($model) || empty($changes))
                            continue;
                        
                   		/*
                        * Owner changed
                        */
                        if(isset($changes[DAO_Ticket::OWNER_ID])) {
                            @$owner_id = $changes[DAO_Ticket::OWNER_ID];
                            if(!empty($owner_id['to'])) {
                                $target_worker = DAO_Worker::get($changes[DAO_Ticket::OWNER_ID]['to']);
                                Event_Cerb5BlogOwnerAssigned::trigger($object_id, $target_worker->id);
                                Event_Cerb5BlogOwnerAssignedGroup::trigger($object_id, $target_worker->id,$model[DAO_Ticket::TEAM_ID]); 
                            }
                            
                        }
                    }
				break;
		}
	}

    
};

