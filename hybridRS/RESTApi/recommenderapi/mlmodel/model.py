from typing import Any
import torch
from torch.nn import Module, ReLU, Softmax
from torch import nn
from torch.utils.data import DataLoader, Dataset
try:
    from mlmodel.dataRetriever import collect_data_for_query, parse_datadict, identifier_to_section, get_all_past_queries, create_tag_pretraining_data, get_all_sections_for_crs, section_to_identifier
except ImportError:
    from dataRetriever import collect_data_for_query, parse_datadict, identifier_to_section, get_all_past_queries, create_tag_pretraining_data, get_all_sections_for_crs, section_to_identifier
import random
import os

# EncoderNames: tag, recquery, pastquery, pastclicked
# difficulty still needs implementation!!

class Encoder(Module):
    def __init__(self, input_dim, latent_dim):
        super(Encoder, self).__init__()
        self.input_dim = input_dim
        self.latent_dim = latent_dim
        self.relu = ReLU()
        self.linear = torch.nn.Linear(self.input_dim, self.latent_dim)

    def resize_weights(self, new_input_size):
        # Get the weights and biases from the source layer
        source_weights = self.linear.weight
        source_biases = self.linear.bias

        # Determine the original input size and output size
        original_input_size = source_weights.size(1)
        original_output_size = source_weights.size(0)

        # Create a new linear layer with the new input size and original output size
        new_layer = nn.Linear(new_input_size, original_output_size)

        # Initialize the weights of the new layer with zeros
        new_layer.weight.data.zero_()
        new_layer.bias.data.zero_()

        # Copy the weights and biases from the source layer to the new layer
        new_layer.weight.data[:, :original_input_size] = source_weights
        new_layer.bias.data = source_biases
        self.linear = new_layer
        self.input_dim = new_input_size

    def forward(self, x):
        x = self.relu(self.linear(x))
        return x
    
    
class Decoder(Module):
    def __init__(self, latent_dim, output_dim):
        super(Decoder, self).__init__()
        self.latent_dim = latent_dim
        self.output_dim = output_dim
        self.relu = ReLU()
        self.linear = torch.nn.Linear(self.latent_dim, self.output_dim)
        self.pre_linear = torch.nn.Linear(self.latent_dim, self.latent_dim)

    def resize_weights(self, new_output_size):
        # Get the weights and biases from the source layer
        source_weights = self.linear.weight
        source_biases = self.linear.bias

        # Determine the original input size and output size
        original_input_size = source_weights.size(1)
        original_output_size = source_weights.size(0)

        # Create a new linear layer with the new input size and original output size
        new_layer = nn.Linear(original_input_size, new_output_size)

        # Initialize the weights of the new layer with zeros
        new_layer.weight.data.zero_()
        new_layer.bias.data.zero_()

        # Copy the weights and biases from the source layer to the new layer
        new_layer.weight.data[:original_output_size,:] = source_weights
        new_layer.bias.data[:original_output_size] = source_biases
        self.linear = new_layer
        self.output_dim = new_output_size

    def forward(self, x):
        x = self.relu(self.pre_linear(x))
        x = self.linear(x)
        return x
    
class MultiEncoderDecoder(Module):
    """A class that combines multiple encoders of various input size, concatenates their latent representations, and then decodes them."""
    def __init__(self, input_dims, latent_dim, output_dim):
        super(MultiEncoderDecoder, self).__init__()
        self.input_dims = input_dims
        self.latent_dim = latent_dim
        self.output_dim = output_dim
        self.encoders = nn.ModuleList([Encoder(input_dim, latent_dim) for input_dim in self.input_dims])
        self.decoder = Decoder(len(self.encoders)*self.latent_dim, self.output_dim)

    def forward(self, x):
        latent_representations = []
        for i, encoder in enumerate(self.encoders):
            latent_representations.append(encoder(x[i]))
        
        latent_representations = torch.cat(latent_representations, dim=-1)
        return self.decoder(latent_representations)
    
    def resize_weights(self, new_input_sizes, new_output_size):
        for i, encoder in enumerate(self.encoders):
            if new_input_sizes[i] != self.input_dims[i] and new_input_sizes[i] != 0 and new_input_sizes[i] != None:
                encoder.resize_weights(new_input_sizes[i])
                self.input_dims[i] = new_input_sizes[i]
        if new_output_size != self.output_dim and new_output_size != 0 and new_output_size != None:
            self.decoder.resize_weights(new_output_size)
            self.output_dim = new_output_size
        self.output_dim = new_output_size

    def get_encoder(self, index):
        return self.encoders[index]

    def get_decoder(self):
        return self.decoder

    def get_input_dims(self):
        return self.input_dims

    def get_output_dim(self):
        return self.output_dim

    def get_latent_dim(self):
        return self.latent_dim

    def get_num_encoders(self):
        return len(self.encoders)



class ModelHandler():
    def __init__(self, encoder_types, tags, items, crs_id, load_checkpoint=True) -> None:
        encoder_types = sorted(encoder_types)
        self.index_to_encoder_type = {i: enc for i, enc in enumerate(encoder_types)}
        self.encoder_types_to_index = {enc: i for i, enc in enumerate(encoder_types)}
        self.tags = tags
        self.tags_to_index = {tag: i for i, tag in enumerate(tags)}
        self.items = items
        self.items_to_index = {item: i for i, item in enumerate(items)}
        self.input_dims = [len(tags) if enc == 'tag' else len(items) for enc in encoder_types]
        self.model = MultiEncoderDecoder(self.input_dims, latent_dim=20, output_dim=len(items))
        self.criterion = torch.nn.CrossEntropyLoss()
        self.lr = 0.005
        self.optimizer = torch.optim.Adam(self.model.parameters(), lr=self.lr)
        self.highest_probit_observed = 0
        self.lowest_probit_observed = 0
        #Maybe set path as environment variable? This would only work on our server
        self.checkpoint_dir = "/var/www/ILIAS-7.19/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/RecommenderSystem/hybridRS/RESTApi/recommenderapi/mlmodel/checkpoints"
        self.model_name = str(crs_id) + "_" + "_".join(list(self.encoder_types_to_index.keys()))
        if self.model_name + ".pt" in os.listdir(self.checkpoint_dir) and load_checkpoint:
            try:
                self.model.load_state_dict(torch.load(f'{self.checkpoint_dir}/{self.model_name}.pt'))
                print("Model loaded from checkpoint")
            except:
                print("No compatible model found in checkpoint")
        if self.model_name+"_optimizer_state.pt" in os.listdir(self.checkpoint_dir) and load_checkpoint:
            try:
                self.optimizer.load_state_dict(torch.load(f'{self.checkpoint_dir}/{self.model_name}_optimizer_state.pt'))
                print("Optimizer state loaded from checkpoint")
            except:
                print("No compatible optimizer state found in checkpoint")

    
    def encode(self, data_dict):
        tag_input = data_dict['TAG_INPUT']
        past_queries = data_dict['PAST_QUERIES']
        past_recommendations = data_dict['PAST_RECOMMENDATIONS']
        queried_sections = data_dict['QUERIED_SECTIONS']
        if "TARGET_RECOMMENDATIONS" in data_dict:
            target_recommendations = data_dict['TARGET_RECOMMENDATIONS']
        
        #check if new tags and add them
        for tag in tag_input:
            if tag not in self.tags:                
                self.add_tag(tag)
        
        #check if new items and add them
        item_identifiers = [item[0] for item in past_queries] + [item[0] for item in past_recommendations] + [item[0] for item in queried_sections]
        if "TARGET_RECOMMENDATIONS" in data_dict:
            item_identifiers += [item[0] for item in target_recommendations]
        for item in item_identifiers:
            if item not in self.items:
                self.add_item(item)
        
        all_input = []
        
        #create one-hot encodings for tags
        if "tag" in self.encoder_types_to_index:
            tag_input_encodings = torch.zeros(len(self.tags))
            tag_indices = torch.tensor([self.tags_to_index[tag] for tag in tag_input])
            tag_input_encodings[tag_indices] = 1
            tag_input_encodings = (tag_input_encodings, self.encoder_types_to_index['tag'])
            all_input.append(tag_input_encodings)
        
        #create weighted encodings for past queries
        if "pastquery" in self.encoder_types_to_index:
            past_queries_encodings = torch.zeros(len(self.items))
            for item, importance in past_queries:
                past_queries_encodings[self.items_to_index[item]] = importance
            past_queries_encodings = (past_queries_encodings, self.encoder_types_to_index['pastquery'])
            all_input.append(past_queries_encodings)
            
        #create weighted encodings for past recommendations
        if "pastclicked" in self.encoder_types_to_index:
            past_recommendations_encodings = torch.zeros(len(self.items))
            for item, importance in past_recommendations:
                past_recommendations_encodings[self.items_to_index[item]] = importance
            past_recommendations_encodings = (past_recommendations_encodings, self.encoder_types_to_index['pastclicked'])
            all_input.append(past_recommendations_encodings)
        
        #create one-hot encodings for queried sections
        if "recquery" in self.encoder_types_to_index:
            queried_sections_encodings = torch.zeros(len(self.items))
            item_indices = torch.tensor([self.items_to_index[item[0]] for item in queried_sections])
            queried_sections_encodings[item_indices] = 1
            queried_sections_encodings = (queried_sections_encodings, self.encoder_types_to_index['recquery'])
            all_input.append(queried_sections_encodings)
            
        
        #Arrange in correct encoder order
        all_input.sort(key=lambda x: x[1])
        all_input = [x[0] for x in all_input]
        
        if "TARGET_RECOMMENDATIONS" in data_dict:
            target_recommendations_encodings = torch.zeros(len(self.items))
            for item, importance in target_recommendations:
                target_recommendations_encodings[self.items_to_index[item]] = importance
            return all_input, target_recommendations_encodings
        
        return all_input, None
    
    def __call__(self, usr_id, crs_id, section_ids, material_types, timestamp):
        data = collect_data_for_query(usr_id, crs_id, section_ids, material_types, timestamp)
        data_dict = parse_datadict(data)
        all_input, _ = self.encode(data_dict)
        
        #only items in crs are valid outputs
        crs_items = [section_to_identifier(x[0], x[1]) for x in get_all_sections_for_crs(crs_id)]
        mask = torch.zeros(len(self.items))
        for item in crs_items:
            mask[self.items_to_index[item]] = 1
        
        #Remove queried items from mask
        queried_items = torch.tensor([self.items_to_index[item[0]] for item in data_dict["QUERIED_SECTIONS"]])
        mask[queried_items] = 0
        
        
        #predict
        output = self.predict(all_input, mask)
                
        predictions = []
        for item, score in output.items():
            if score < 0.2:
                continue
            section_id, material_type = identifier_to_section(item)
            predictions.append({"section_id": section_id, "material_type": material_type, "score": score})
        
        #separate section_id and material_type
        parsed_output = {"usr_id": usr_id, 
                         "crs_id": crs_id,
                         "predictions": predictions}
        
        return parsed_output
    
    def train(self):
        self.model = MultiEncoderDecoder(self.input_dims, latent_dim=20, output_dim=len(self.items))
        self.optimizer = torch.optim.Adam(self.model.parameters(), lr=self.lr)

        all_past_queries = get_all_past_queries()
        all_samples = []
        for usr_id, crs_id, material_id, material_type, timestamp in all_past_queries:
            data = collect_data_for_query(usr_id, crs_id, [material_id], [material_type], timestamp, retrieve_targets=True)
            data_dict = parse_datadict(data)
            if data_dict["TAG_INPUT"] == []:
                #Item has been removed from the t_p_s table
                continue
            all_inputs, targets = self.encode(data_dict)
            if torch.sum(targets) != 0:
                all_samples.append(tuple(all_inputs) + (targets,))
        
    
        tag_pretraining_data = create_tag_pretraining_data(200)
        for item in tag_pretraining_data:
            all_inputs, targets = self.encode(item)
            all_samples.append(tuple(all_inputs) + (targets,))
        
        class RecSysDataset(Dataset):
            def __init__(self, samples):
                self.samples = samples                    
            def __len__(self):
                return len(self.samples)
            def __getitem__(self, index):
                return self.samples[index]
        
        random.shuffle(all_samples)
        

        train_size = int(0.8 * len(all_samples))
        train_set = RecSysDataset(all_samples[:train_size])
        val_set = RecSysDataset(all_samples[train_size:])
        self.inner_train(train_set, val_set, batch_size=1)
        print("Training finished")
    
    def update(self, usr_id, crs_id, section_ids, material_types, timestamp):
        data = collect_data_for_query(usr_id, crs_id, section_ids, material_types, timestamp)
        data_dict = parse_datadict(data)
        all_input, target = self.encode(data_dict)
        if target is not None:
            self._step(all_input, target)
    
    def predict(self, inputs, crs_mask):
        if self.model.training:
            self.model.eval()
        with torch.no_grad():
            preds = self.model(inputs)
        preds = torch.exp(preds) / (1 + torch.exp(preds))
        preds = preds * crs_mask
        scored_items = {}
        for i, pred in enumerate(preds):
            item = self.items[i]
            scored_items[item] = pred.item()  #Scale to positive classification area
        return scored_items
    
    def _step(self, inputs, labels):
        if not self.model.training:
            self.model.train()
        self.optimizer.zero_grad()
        outputs = self.model(inputs)
        loss = self.criterion(outputs, labels)
        loss.backward()
        self.optimizer.step()
        return loss.item(), outputs
        
    def add_tag(self, tag):
        self.tags.append(tag)
        self.tags_to_index[tag] = len(self.tags) - 1 
               
        tag_index = self.encoder_types_to_index['tag']
        temp_input_dims = list(self.model.input_dims)
        temp_input_dims[tag_index] += 1
        self.model.resize_weights(temp_input_dims, self.model.output_dim)
    
    def add_item(self, item):
        self.items.append(item)
        self.items_to_index[item] = len(self.items) - 1
        
        non_tag_indices = [idx for type, idx in self.encoder_types_to_index.items() if type != 'tag']
        temp_input_dims = list(self.model.input_dims)
        for idx in non_tag_indices:
            temp_input_dims[idx] += 1
        self.model.resize_weights(temp_input_dims, self.model.output_dim + 1)
            
    def inner_train(self, train_dataset, val_dataset, batch_size):
        
        
        train_loader = DataLoader(train_dataset, batch_size=batch_size, shuffle=True)
        val_loader = DataLoader(val_dataset, batch_size=batch_size, shuffle=True)
        # Train until validation loss stops decreasing
        prev_val_loss = float('inf')
        while True:
            # Save model if validation loss decreases
            torch.save(self.model.state_dict(), f'{self.checkpoint_dir}/{self.model_name}.pt')
            # save optimizer state
            torch.save(self.optimizer.state_dict(), f'{self.checkpoint_dir}/{self.model_name}_optimizer_state.pt')
            
            # Train for one epoch
            self.model.train()
            train_loss = 0
            for i, inputs_and_labels in enumerate(train_loader):
                #inputs_and_labels = torch.tensor(inputs_and_labels, dtype=torch.float32)
                inputs = inputs_and_labels[:-1]
                labels = inputs_and_labels[-1]
                labels = labels / torch.sum(labels, dim=-1, keepdim=True)
                loss, outputs = self._step(inputs, labels)
                train_loss += loss
            train_loss /= len(train_loader)
            print(f'Training loss: {train_loss}')
            
            # Validate
            self.model.eval()
            val_loss = 0
            with torch.no_grad():
                for i, inputs_and_labels in enumerate(val_loader):
                    inputs = inputs_and_labels[:-1]
                    labels = inputs_and_labels[-1]
                    labels = labels / torch.sum(labels, dim=-1, keepdim=True)
                    outputs = self.model(inputs)
                    loss = self.criterion(outputs, labels)
                    #print(f"Inputs: {inputs}, Labels: {labels}, Outputs: {outputs}, Loss: {loss}")
                    val_loss += loss.item()
                val_loss /= len(val_loader)
                print(f'Validation loss: {val_loss}')
            if val_loss >= prev_val_loss:
                break
                
            prev_val_loss = val_loss
        
        # Load best model
        self.model.load_state_dict(torch.load(f'{self.checkpoint_dir}/{self.model_name}.pt'))
        # Load optimizer state
        self.optimizer.load_state_dict(torch.load(f'{self.checkpoint_dir}/{self.model_name}_optimizer_state.pt'))